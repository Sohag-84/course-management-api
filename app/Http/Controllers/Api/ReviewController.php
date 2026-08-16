<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // GET /api/courses/{course}/reviews
    public function index(Course $course)
    {
        $reviews = $course->reviews()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ], 200);
    }

    // POST /api/courses/{course}/reviews
    public function store(Request $request, Course $course)
    {
        $user = $request->user();
        $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
        if (!$isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'You must be enrolled in this course to leave a review',
            ], 403);
        }

        //check user already give review or not
        $alreadyReviewed = Review::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this course',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            "comment" => 'nullable|string|max:1000'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $review = $course->reviews()->create([
            'user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $review->load('user:id,name');
        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    // PATCH /api/courses/{course}/reviews/{review}
    public function update(Request $request, Course $course, Review $review)
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this review',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ], 422);
        }

        $review->update($request->only('rating', 'comment'));

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review,
        ], 200);
    }

    // DELETE /api/courses/{course}/reviews/{review}
    public function destroy(Request $request, Course $course, Review $review)
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorize to delete this review',
            ], 403);
        }

        $review->delete();

        return response()->json([
            "success" => true,
            'message' => "Review deleted successfully"
        ], 200);
    }
}
