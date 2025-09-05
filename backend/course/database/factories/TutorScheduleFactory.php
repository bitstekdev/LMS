<?php

namespace Database\Factories;

use App\Models\TutorCategory;
use App\Models\TutorSchedule;
use App\Models\TutorSubject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorScheduleFactory extends Factory
{
    protected $model = TutorSchedule::class;

    public function definition(): array
    {
        return [
            'tutor_id' => User::where('role', 'instructor')->inRandomOrder()->first()->id ?? null,
            'category_id' => TutorCategory::inRandomOrder()->first()->id ?? TutorCategory::factory(),
            'subject_id' => TutorSubject::inRandomOrder()->first()->id ?? TutorSubject::factory(),
            'start_time' => fake()->word,
            'end_time' => fake()->word,
            'tution_type' => fake()->word,
            'duration' => fake()->word,
            'description' => fake()->word,
            'status' => fake()->word,
        ];
    }
}
