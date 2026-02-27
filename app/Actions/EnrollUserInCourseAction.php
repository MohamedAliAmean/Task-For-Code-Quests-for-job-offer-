<?php

namespace App\Actions;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollUserInCourseAction
{
    public function execute(User $user, Course $course): CourseEnrollment
    {
        return DB::transaction(function () use ($user, $course) {
            $lockedCourse = Course::query()
                ->whereKey($course->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $existing = CourseEnrollment::query()
                ->where('user_id', $user->getKey())
                ->where('course_id', $lockedCourse->getKey())
                ->first();

            if ($existing) {
                return $existing;
            }

            if (! $lockedCourse->isPublished()) {
                throw ValidationException::withMessages([
                    'course' => 'You can only enroll in published courses.',
                ]);
            }

            $now = now();

            CourseEnrollment::query()->insertOrIgnore([
                'user_id' => $user->getKey(),
                'course_id' => $lockedCourse->getKey(),
                'enrolled_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return CourseEnrollment::query()
                ->where('user_id', $user->getKey())
                ->where('course_id', $lockedCourse->getKey())
                ->firstOrFail();
        });
    }
}

