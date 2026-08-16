<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{

    //this user can update this course?
    public function update(User $user, Course $course): bool
    {
        return $user->id == $course->user_id;
    }

    //user can delete this course?
    public function delete(User $user, Course $course): bool
    {
        return $user->id == $course->user_id;
    }

    //can user manage this course lessons
    public function manageLessons(User $user, Course $course): bool
    {
        return $user->id == $course->user_id;
    }
}
