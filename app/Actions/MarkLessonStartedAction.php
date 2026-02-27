<?php

namespace App\Actions;

use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class MarkLessonStartedAction
{
    public function execute(User $user, Lesson $lesson): void
    {
        $isEnrolled = CourseEnrollment::query()
            ->where('user_id', $user->getKey())
            ->where('course_id', $lesson->course_id)
            ->exists();

        if (! $isEnrolled) {
            throw ValidationException::withMessages([
                'course' => 'You must be enrolled to track lesson progress.',
            ]);
        }

        $now = now();

        LessonProgress::query()->insertOrIgnore([
            'user_id' => $user->getKey(),
            'lesson_id' => $lesson->getKey(),
            'started_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        LessonProgress::query()
            ->where('user_id', $user->getKey())
            ->where('lesson_id', $lesson->getKey())
            ->whereNull('started_at')
            ->update([
                'started_at' => $now,
                'updated_at' => $now,
            ]);
    }
}

