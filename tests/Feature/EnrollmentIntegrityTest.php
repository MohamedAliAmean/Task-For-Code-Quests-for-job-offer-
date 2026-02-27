<?php

use App\Actions\EnrollUserInCourseAction;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

it('enrolls idempotently and prevents duplicates', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();

    $action = app(EnrollUserInCourseAction::class);

    $first = $action->execute($user, $course);
    $second = $action->execute($user, $course);

    expect($first->getKey())->toBe($second->getKey());

    expect(CourseEnrollment::query()
        ->where('user_id', $user->getKey())
        ->where('course_id', $course->getKey())
        ->count()
    )->toBe(1);
});

it('prevents enrolling in draft courses', function () {
    $user = User::factory()->create();
    $course = Course::factory()->draft()->create();

    $action = app(EnrollUserInCourseAction::class);

    $action->execute($user, $course);
})->throws(ValidationException::class);

