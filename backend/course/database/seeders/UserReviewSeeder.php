<?php

namespace Database\Seeders;

use App\Models\UserReview;
use Illuminate\Database\Seeder;

class UserReviewSeeder extends Seeder
{
    public function run(): void
    {
        UserReview::factory()->count(15)->create();
    }
}
