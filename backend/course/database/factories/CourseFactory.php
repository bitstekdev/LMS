<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'category_id' => Category::inRandomOrder()->first()->id ?? Category::factory(),
            'title' => fake()->sentence,
            'slug' => fake()->slug,
            'short_description' => fake()->paragraph,
            'course_type' => 'general',
            'status' => 'active',
            'level' => 'beginner',
            'language' => 'english',
            'is_paid' => rand(0, 1),
            'price' => 99.99,
            'enable_drip_content' => false,
            'meta_description' => fake()->sentence,
            'average_rating' => 0,
        ];
    }
}
