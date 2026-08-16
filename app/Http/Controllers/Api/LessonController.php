<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    // GET /api/courses/{course}/lessons
    public function index(Course $course)
    {
        $lessons = $course->lessons;
        return response()->json(data: ['success' => true, "data" => LessonResource::collection($lessons)]);
    }

    // POST /api/courses/{course}/lessons
    public function store(Request $request, Course $course)
    {
        //only owners can add lesson
        // if ($course->user_id !== $request->user()->id) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You are not authorized to add lessons to this course',
        //     ], 403);
        // }
        if ($request->user()->cannot('manageLessons', $course)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage lessons in this course',
            ], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|string|max:255',
                "content" => 'nullable|string',
                "video_url" => 'nullable|url',
                // 'order' => 'nullable|integer|min:0'
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        // এই course এর সর্বশেষ order বের করে +1 করো
        $nextOrder = $course->lessons()->max('order') + 1;

        $lesson = $course->lessons()->create([
            'title' => $request->title,
            'content' => $request->content,
            'video_url' => $request->video_url,
            'order' => $nextOrder
        ]);

        return response()->json([
            'success' => true,
            'message' => "Lesson created successfully",
            'data' => new LessonResource($lesson)
        ], 201);
    }

    // GET /api/courses/{course}/lessons/{lesson}
    public function show(Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson does not belong to this course',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new LessonResource($lesson),
        ], 200);
    }

    // PUT/PATCH /api/courses/{course}/lessons/{lesson}
    public function update(Request $request, Course $course, Lesson $lesson)
    {
        // if ($course->user_id !== $request->user()->id) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You are not authorized to update this lesson',
        //     ], 403);
        // }
        if ($request->user()->cannot('manageLessons', $course)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage lessons in this course',
            ], 403);
        }

        if ($lesson->course_id !== $course->id) {
            return response()->json(['success' => false, 'message' => "Lesson doesn't belogn to this course",], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'sometimes|required|string|max:255',
                "content" => 'nullable|string',
                "video_url" => 'nullable|url',
                'order' => 'nullable|integer|min:0'
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $lesson->update($request->only('title', 'content', 'video_url', 'order'));

        return response()->json([
            'success' => true,
            'message' => 'Lesson updated successfully',
            'data' => new LessonResource($lesson),
        ], 200);
    }

    // DELETE /api/courses/{course}/lessons/{lesson}
    public function destroy(Request $request, Course $course, Lesson $lesson)
    {
        // if ($course->user_id !== $request->user()->id) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You are not authorized to delete this lesson',
        //     ], 403);
        // }
        if ($request->user()->cannot('manageLessons', $course)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage lessons in this course',
            ], 403);
        }

        if ($course->id !== $lesson->course_id) {
            return response()->json(['success' => false, 'message' => "Lesson doesn't belogn to this course",], 404);
        }
        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully',
        ], 200);
    }


    // PATCH /api/courses/{course}/lessons/reorder
    public function reorder(Request $request, Course $course)
    {
        // if ($course->user_id !== $request->user()->id) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You are not authorized to reorder lessons in this course',
        //     ], 403);
        // }
        if ($request->user()->cannot('manageLessons', $course)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage lessons in this course',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'lessons' => 'required|array|min:1',
            'lessons.*.id' => 'required|integer|exists:lessons,id',
            'lessons.*.order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // নিরাপত্তা: পাঠানো সব lesson id আসলেই এই course এর কিনা, তা যাচাই করো
        $lessonIds = collect($request->lessons)->pluck('id');
        $validCount = $course->lessons()->whereIn('id', $lessonIds)->count();

        if ($validCount !== $lessonIds->count()) {
            return response()->json([
                'success' => false,
                'message' => 'One or more lessons do not belong to this course',
            ], 422);
        }

        // একসাথে সব update — transaction দিয়ে wrap করা (all-or-nothing)
        DB::transaction(function () use ($request) {
            foreach ($request->lessons as $item) {
                Lesson::where('id', $item['id'])->update(['order' => $item['order']]);
            }
        });

        $updatedLessons = $course->lessons; // relationship-এ orderBy আছে, তাই এমনিতেই sorted আসবে

        return response()->json([
            'success' => true,
            'message' => 'Lessons reordered successfully',
            'data' => LessonResource::collection($updatedLessons),
        ], 200);
    }
}
