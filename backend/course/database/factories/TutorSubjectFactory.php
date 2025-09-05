<?php

namespace Database\Factories;

use App\Models\TutorSubject;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorSubjectFactory extends Factory
{
    protected $model = TutorSubject::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'slug' => fake()->word,
            'status' => fake()->word,
        ];
    }
}
