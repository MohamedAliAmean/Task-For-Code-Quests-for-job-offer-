<?php

use App\Actions\EnrollUserInCourseAction;
use App\Actions\MarkLessonCompletedAction;
use App\Events\CourseCompleted;
use App\Listeners\SendCourseCompletionEmail;
use App\Mail\CourseCompletedMail;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('creates exactly one certificate and sends completion email once', function () {
    Mail::fake();

    $user = User::factory()->create();
    $course = Course::factory()->create();

    $lesson1 = Lesson::factory()->create([
        'course_id' => $course->getKey(),
        'position' => 1,
        'is_required' => true,
        'is_preview' => false,
    ]);

    $lesson2 = Lesson::factory()->create([
        'course_id' => $course->getKey(),
        'position' => 2,
        'is_required' => true,
        'is_preview' => false,
    ]);

    app(EnrollUserInCourseAction::class)->execute($user, $course);

    $completeLesson = app(MarkLessonCompletedAction::class);

    expect($completeLesson->execute($user, $lesson1))->toBeFalse();
    expect($completeLesson->execute($user, $lesson2))->toBeTrue();

    expect(CourseCertificate::query()
        ->where('user_id', $user->getKey())
        ->where('course_id', $course->getKey())
        ->count()
    )->toBe(1);

    Mail::assertSent(CourseCompletedMail::class, 1);

    $certificate = CourseCertificate::query()->firstOrFail();

    app(SendCourseCompletionEmail::class)->handle(new CourseCompleted(
        certificateId: $certificate->getKey(),
        userId: (int) $user->getKey(),
        courseId: (int) $course->getKey(),
    ));

    Mail::assertSent(CourseCompletedMail::class, 1);
});

