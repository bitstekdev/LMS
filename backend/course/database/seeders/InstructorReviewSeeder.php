<?php

namespace Database\Seeders;

use App\Models\InstructorReview;
use Illuminate\Database\Seeder;

class InstructorReviewSeeder extends Seeder
{
    public function run(): void
    {
        InstructorReview::factory()->count(10)->create();
    }
}
