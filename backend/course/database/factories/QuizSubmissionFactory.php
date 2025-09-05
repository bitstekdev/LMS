<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizSubmissionFactory extends Factory
{
    protected $model = QuizSubmission::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::inRandomOrder()->first()?->id ?? Quiz::factory(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'correct_answer' => fake()->text(100),
            'wrong_answer' => fake()->text(100),
            'submits' => fake()->paragraph(),
        ];
    }
}
