<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\LiveClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LiveClassFactory extends Factory
{
    protected $model = LiveClass::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
            'class_topic' => fake()->sentence(),
            'provider' => fake()->randomElement(['Zoom', 'Google Meet', 'Teams']),
            'class_date_and_time' => fake()->dateTimeBetween('+1 days', '+1 month'),
            'additional_info' => fake()->paragraph(),
            'note' => fake()->sentence(),
        ];
    }
}
