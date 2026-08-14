<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
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


    Route::apiResource('courses', CourseController::class);

    // Nested route: courses এর ভিতরে lessons
    Route::apiResource('courses.lessons', LessonController::class);
});
