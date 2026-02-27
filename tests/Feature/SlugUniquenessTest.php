<?php

use App\Models\Course;
use Illuminate\Database\QueryException;

it('enforces unique course slugs even with soft deletes', function () {
    $slug = 'unique-course-slug';

    $course = Course::factory()->create([
        'slug' => $slug,
    ]);

    $course->delete();

    Course::factory()->create([
        'slug' => $slug,
    ]);
})->throws(QueryException::class);

