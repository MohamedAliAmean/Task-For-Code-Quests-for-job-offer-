<?php

namespace App\Actions;

use App\Events\CourseCompleted;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueCourseCertificateAction
{
    /**
     * Attempt to issue a certificate if the user completed all currently-required lessons.
     *
     * Returns true only when a new certificate is created.
     */
    public function execute(User $user, Course $course): bool
    {
        if (CourseCertificate::query()
            ->where('user_id', $user->getKey())
            ->where('course_id', $course->getKey())
            ->exists()
        ) {
            return false;
        }

        $requiredLessonCount = Lesson::query()
            ->where('course_id', $course->getKey())
            ->where('is_required', true)
            ->whereNull('deleted_at')
            ->count();

        if ($requiredLessonCount === 0) {
            return false;
        }

        $completedRequiredLessonCount = LessonProgress::query()
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->where('lesson_progress.user_id', $user->getKey())
            ->whereNotNull('lesson_progress.completed_at')
            ->where('lessons.course_id', $course->getKey())
            ->where('lessons.is_required', true)
            ->whereNull('lessons.deleted_at')
            ->count();

        if ($completedRequiredLessonCount < $requiredLessonCount) {
            return false;
        }

        $now = now();
        $certificateId = (string) Str::uuid();

        $inserted = DB::table('course_certificates')->insertOrIgnore([
            'id' => $certificateId,
            'user_id' => $user->getKey(),
            'course_id' => $course->getKey(),
            'issued_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($inserted !== 1) {
            return false;
        }

        event(new CourseCompleted(
            certificateId: $certificateId,
            userId: (int) $user->getKey(),
            courseId: (int) $course->getKey(),
        ));

        return true;
    }
}

