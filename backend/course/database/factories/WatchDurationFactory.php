<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WatchDuration>
 */
class WatchDurationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'watched_student_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'watched_course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
            'watched_lesson_id' => Lesson::inRandomOrder()->first()?->id ?? Lesson::factory(),
            'current_duration' => fake()->numberBetween(10, 3600),
            'watched_counter' => json_encode(['1x', '2x', 'resume']),
        ];
    }
}
