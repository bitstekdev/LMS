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
        $instructor = User::where('role', 'instructor')->inRandomOrder()->first()
            ?? User::factory()->create(['role' => 'instructor']);

        return [
            'instructor_id' => $instructor->id,
            'category_id' => TutorCategory::inRandomOrder()->first()->id ?? TutorCategory::factory(),
            'subject_id' => TutorSubject::inRandomOrder()->first()->id ?? TutorSubject::factory(),
            'description' => fake()->sentence(10),
            'thumbnail' => fake()->imageUrl(640, 480, 'education', true, 'subject'),
            'price' => fake()->randomFloat(2, 10, 200),
        ];
    }
}
