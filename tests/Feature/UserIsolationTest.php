<?php

use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\User;

it('prevents users from viewing certificates they do not own', function () {
    $course = Course::factory()->create();

    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $certificate = CourseCertificate::factory()->create([
        'user_id' => $owner->getKey(),
        'course_id' => $course->getKey(),
    ]);

    $this->actingAs($intruder)
        ->get(route('certificates.show', $certificate))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('certificates.show', $certificate))
        ->assertOk();
});

