<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    // POST /api/courses/{course}/enroll
    public function enroll(Request $request, Course $course)
    {
        $user = $request->user();

        //check user already enrolled this course or not
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course',
            ], 422);
        }

        $user->enrolledCourses()->attach($course->id, ['enrolled_at' => now()]);
        return response()->json([
            'success' => true,
            'message' => 'Enrolled successfully',
        ], 201);
    }

    // DELETE /api/courses/{course}/enroll
    public function unenroll(Request $request, Course $course)
    {
        $user = $request->user();

        //check user already enrolled this course or not
        //if user don't enrolled this course, then he don't get permission to delete this enrollment
        if (!$user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled this course',
            ], 422);
        }

        $user->enrolledCourses()->detach($course->id);

        return response()->json([
            'success' => true,
            'message' => 'Unenrolled successfully',
        ], 200);
    }

    // GET /api/my-courses
    public function myEnrolledCourses(Request $request, Course $course)
    {
        $courses = $request->user()->enrolledCourses()->with('user:id,name')->latest()->paginate(10);
        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses),
        ], 200);
    }

    // GET /api/courses/{course}/students
    //for instructor to showing number of enroll student for his course
    public function enrolledStudents(Request $request, Course $course)
    {
        if ($course->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this course\'s students',
            ], 403);
        }

        $students = $course->enrolledStudents()->select('users.id', 'users.name', 'users.email')->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $students,
        ], 200);
    }
}
