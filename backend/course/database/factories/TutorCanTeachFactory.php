<?php

namespace Database\Factories;

use App\Models\TutorCanTeach;
use App\Models\TutorCategory;
use App\Models\TutorSubject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorCanTeachFactory extends Factory
{
    protected $model = TutorCanTeach::class;

    public function definition(): array
    {
        return [
            'instructor_id' => User::where('role', 'instructor')->inRandomOrder()->first()->id ?? null,
            'category_id' => TutorCategory::inRandomOrder()->first()->id ?? TutorCategory::factory(),
            'subject_id' => TutorSubject::inRandomOrder()->first()->id ?? TutorSubject::factory(),
            'description' => fake()->word,
            'thumbnail' => fake()->word,
            'price' => fake()->word,
        ];
    }
}
