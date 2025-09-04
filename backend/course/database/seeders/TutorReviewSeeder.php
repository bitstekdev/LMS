<?php

namespace Database\Seeders;

use App\Models\TutorReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class TutorReviewSeeder extends Seeder
{
    public function run(): void
    {
        $tutor = User::where('role', 'instructor')->first();
        $student = User::where('role', 'student')->first();

        if ($tutor && $student) {
            TutorReview::create([
                'tutor_id' => $tutor->id,
                'student_id' => $student->id,
                'rating' => 5,
                'review' => 'Excellent tutor with deep understanding of concepts.',
            ]);

            TutorReview::create([
                'tutor_id' => $tutor->id,
                'student_id' => $student->id,
                'rating' => 4,
                'review' => 'Very good, but sometimes hard to reach.',
            ]);
        }
    }
}
