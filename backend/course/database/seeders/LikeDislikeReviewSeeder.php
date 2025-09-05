<?php

namespace Database\Seeders;

use App\Models\LikeDislikeReview;
use Illuminate\Database\Seeder;

class LikeDislikeReviewSeeder extends Seeder
{
    public function run(): void
    {
        LikeDislikeReview::factory()->count(10)->create();
    }
}
