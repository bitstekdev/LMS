<?php

namespace Database\Factories;

use App\Models\TutorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorCategoryFactory extends Factory
{
    protected $model = TutorCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'slug' => fake()->word,
            'status' => fake()->word,
        ];
    }
}
