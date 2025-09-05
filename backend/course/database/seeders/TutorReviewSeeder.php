<?php

namespace Database\Seeders;

use App\Models\TutorReview;
use Illuminate\Database\Seeder;

class TutorReviewSeeder extends Seeder
{
    public function run(): void
    {
        TutorReview::factory()->count(10)->create();
    }
}
