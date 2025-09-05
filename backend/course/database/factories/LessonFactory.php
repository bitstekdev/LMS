<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Section;
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
            'course_id' => Course::inRandomOrder()->first()->id ?? Course::factory(),
            'section_id' => Section::inRandomOrder()->first()->id ?? Section::factory(),
            'title' => fake()->sentence,
            'lesson_type' => 'video',
            'duration' => '5:00',
            'is_free' => 1,
        ];
    }
}
