<?php

namespace Database\Seeders;

use App\Models\TutorCategory;
use Illuminate\Database\Seeder;

class TutorCategorySeeder extends Seeder
{
    public function run(): void
    {
        TutorCategory::factory()->count(10)->create();
    }
}
