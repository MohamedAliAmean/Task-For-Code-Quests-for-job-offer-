<?php

namespace App\Actions;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarkLessonCompletedAction
{
    public function __construct(
        private readonly IssueCourseCertificateAction $issueCourseCertificate,
    ) {
    }

    /**
     * Marks a lesson as completed (idempotent) and issues a certificate when eligible.
     *
     * Returns true only when a new course certificate is created.
     */
    public function execute(User $user, Lesson $lesson): bool
    {
        return DB::transaction(function () use ($user, $lesson) {
            $isEnrolled = CourseEnrollment::query()
                ->where('user_id', $user->getKey())
                ->where('course_id', $lesson->course_id)
                ->exists();

            if (! $isEnrolled) {
                throw ValidationException::withMessages([
                    'course' => 'You must be enrolled to complete lessons.',
                ]);
            }

            $now = now();

            LessonProgress::query()->insertOrIgnore([
                'user_id' => $user->getKey(),
                'lesson_id' => $lesson->getKey(),
                'started_at' => $now,
                'completed_at' => $now,
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

            LessonProgress::query()
                ->where('user_id', $user->getKey())
                ->where('lesson_id', $lesson->getKey())
                ->whereNull('completed_at')
                ->update([
                    'completed_at' => $now,
                    'updated_at' => $now,
                ]);

            $course = Course::query()->findOrFail($lesson->course_id);

            return $this->issueCourseCertificate->execute($user, $course);
        });
    }
}

