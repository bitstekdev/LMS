<?php

namespace Database\Factories;

use App\Models\TutorReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorReviewFactory extends Factory
{
    protected $model = TutorReview::class;

    public function definition(): array
    {
        $tutor = User::where('role', 'instructor')->first();
        $student = User::where('role', 'student')->first();

        return [
            'tutor_id' => $tutor ? $tutor->id : null,
            'student_id' => $student ? $student->id : null,
            'rating' => fake()->word,
            'review' => fake()->word,
        ];
    }
}
