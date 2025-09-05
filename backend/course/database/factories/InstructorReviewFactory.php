<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstructorReview>
 */
class InstructorReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'instructor_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'rating' => fake()->randomElement(['1', '2', '3', '4', '5']),
            'review' => fake()->paragraph(),
        ];
    }
}
