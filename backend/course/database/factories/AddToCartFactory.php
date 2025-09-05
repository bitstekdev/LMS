<?php

namespace Database\Factories;

use App\Models\AddToCart;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddToCartFactory extends Factory
{
    protected $model = AddToCart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
        ];
    }
}
