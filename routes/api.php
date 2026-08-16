<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::get("/user", function (Request $request) {
        return $request->user();
    });

    Route::patch('/courses/{course}/lessons/reorder', [LessonController::class, 'reorder']);

    // Enrollment routes
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enroll']);
    Route::delete('/courses/{course}/enroll', [EnrollmentController::class, 'unenroll']);
    Route::get('/courses/{course}/students', [EnrollmentController::class, 'enrolledStudents']);
    Route::get('/my-courses', [EnrollmentController::class, 'myEnrolledCourses']);


    Route::apiResource('courses', CourseController::class);

    // Nested route: courses এর ভিতরে lessons
    Route::apiResource('courses.lessons', LessonController::class);
    Route::apiResource('courses.reviews',ReviewController::class)->except(['show']);
});
