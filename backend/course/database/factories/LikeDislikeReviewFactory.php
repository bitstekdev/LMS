<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LikeDislikeReview>
 */
class LikeDislikeReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'review_id' => Review::inRandomOrder()->first() ?? Review::factory(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'liked' => fake()->numberBetween(0, 1),
            'disliked' => fake()->numberBetween(0, 1),
        ];
    }
}
