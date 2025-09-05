<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'title' => $this->faker->sentence,
            'slug' => $this->faker->slug,
            'short_description' => $this->faker->paragraph,
            'course_type' => 'general',
            'status' => 'active',
            'level' => 'beginner',
            'language' => 'english',
            'is_paid' => rand(0, 1),
            'price' => 99.99,
            'enable_drip_content' => false,
            'meta_description' => $this->faker->sentence,
            'average_rating' => 0,
        ];
    }
}
