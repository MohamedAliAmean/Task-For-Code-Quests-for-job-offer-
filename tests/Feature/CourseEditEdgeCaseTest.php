<?php

use App\Actions\EnrollUserInCourseAction;
use App\Actions\IssueCourseCertificateAction;
use App\Actions\MarkLessonCompletedAction;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('can complete a course when a required lesson is removed after enrollment', function () {
    Mail::fake();

    $user = User::factory()->create();
    $course = Course::factory()->create();

    $lesson1 = Lesson::factory()->create([
        'course_id' => $course->getKey(),
        'position' => 1,
        'is_required' => true,
    ]);

    $lesson2 = Lesson::factory()->create([
        'course_id' => $course->getKey(),
        'position' => 2,
        'is_required' => true,
    ]);

    app(EnrollUserInCourseAction::class)->execute($user, $course);

    app(MarkLessonCompletedAction::class)->execute($user, $lesson1);

    $lesson2->delete();

    $created = app(IssueCourseCertificateAction::class)->execute($user, $course);

    expect($created)->toBeTrue();

    expect(CourseCertificate::query()
        ->where('user_id', $user->getKey())
        ->where('course_id', $course->getKey())
        ->count()
    )->toBe(1);
});

