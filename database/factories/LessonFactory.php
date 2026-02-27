<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'position' => 1,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'video_url' => 'https://example.com/video.mp4',
            'is_preview' => false,
            'is_required' => true,
        ];
    }
}
