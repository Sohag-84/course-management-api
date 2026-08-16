<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{

    //all course list
    public function index(Request $request)
    {
        // $course = Course::with('user:id,name')->latest()->paginate(perPage: 10);
        $query = Course::with('user:id,name');

        //search: using title and description keywoard
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        //Filter: only published course showing
        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }
        //Filter: min price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        //Filter: max price
        if ($request->filled('max_price')) {
            $query->where('price', "<=", $request->max_price);
        }

        //Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $courses = $query->paginate(10);
        return response()->json([
            'success' => true,
            'message' => "Fetched courses",
            'data' => CourseResource::collection($courses->items()), // শুধু course list
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'next_page_url' => $courses->nextPageUrl(),
                'prev_page_url' => $courses->previousPageUrl(),
            ],
        ], 200);
    }


    public function store(Request $request)
    {
        //create new course
        $validator = Validator::make(data: $request->all(), rules: [
            'title' => 'required|string|max:255',
            "description" => "nullable|string",
            "duration" => 'nullable|string|max:100',
            'price' => "required|numeric|min:0",
            'is_published' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(data: [
                'success' => false,
                'errors' => $validator->errors()
            ], status: 422);
        }

        $course = Course::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'duration' => $request->duration,
            'price' => $request->price,
            'is_published' => $request->is_published ?? false
        ]);
        $course->load('user:id,name');
        return response()->json([
            "success" => true,
            'message' => "Course created successfully",
            'data' => new CourseResource($course)
        ], status: 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        //get single course
        $course->load('user:id,name');
        return response()->json([
            'success' => true,
            'data' => new CourseResource($course)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        //only course owner can update his course
        // if ($request->user()->id !== $course->user_id) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You are not authorized to update this course',
        //     ], 403);
        // }

        if ($request->user()->cannot('update', $course)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this course',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            "duration" => 'nullable|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'is_published' => 'boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $course->update($request->only('title', 'description', 'duration', 'price', 'is_published'));

        return response()->json([
            'success' => true,
            'message' => "Course updated successfully",
            'data' => new CourseResource($course)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Course $course)
    {
        //delete single course
        // if ($course->user_id !== $request->user()->id) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'You are not authorized to delete this course',
        //     ], 403);
        // }
        if ($request->user()->cannot('delete', $course)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this course',
            ], 403);
        }

        $course->delete();

        return response()->json([
            'success' => true,
            "message" => "Course deleted successfully",
            'data' => new CourseResource($course)
        ], 200);
    }
}
