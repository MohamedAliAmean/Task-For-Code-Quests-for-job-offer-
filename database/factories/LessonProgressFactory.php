<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LessonProgress>
 */
class LessonProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-3 days', '-1 day');
        $completedAt = $this->faker->boolean() ? $this->faker->dateTimeBetween($startedAt, 'now') : null;

        return [
            'user_id' => User::factory(),
            'lesson_id' => Lesson::factory(),
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ];
    }
}
