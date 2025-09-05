<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'course_id' => Course::query()->inRandomOrder()->value('id') ?? Course::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'review_type' => 'course',
            'review' => fake()->paragraph(),
        ];
    }
}
