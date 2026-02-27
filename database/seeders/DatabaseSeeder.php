<?php

namespace Database\Seeders;

use App\Enums\CourseLevel;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => true,
            'role' => 'admin',
        ]);

        $courses = Course::factory()
            ->count(3)
            ->sequence(
                ['level' => CourseLevel::Beginner],
                ['level' => CourseLevel::Intermediate],
                ['level' => CourseLevel::Advanced],
            )
            ->create();

        foreach ($courses as $course) {
            foreach (range(1, 6) as $position) {
                Lesson::factory()->create([
                    'course_id' => $course->id,
                    'position' => $position,
                    'title' => "Lesson {$position}",
                    'is_preview' => $position === 1,
                    'is_required' => $position !== 6,
                ]);
            }
        }

        Course::factory()
            ->draft()
            ->create([
                'title' => 'Draft course (should not be enrollable)',
            ]);
    }
}
