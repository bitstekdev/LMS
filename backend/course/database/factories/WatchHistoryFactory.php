<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class WatchHistoryFactory extends Factory
{
    protected $model = WatchHistory::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
            'student_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'completed_lesson' => fake()->text(50),
            'watching_lesson_id' => fake()->randomDigit(),
            'course_progress' => fake()->numberBetween(1, 100),
            'completed_date' => now()->toDateString(),
        ];
    }
}
