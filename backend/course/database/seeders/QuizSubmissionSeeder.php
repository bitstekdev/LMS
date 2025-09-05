<?php

namespace Database\Seeders;

use App\Models\QuizSubmission;
use Illuminate\Database\Seeder;

class QuizSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        QuizSubmission::factory()->count(10)->create();
    }
}
