<?php

namespace Database\Seeders;

use App\Models\BootcampCategory;
use Illuminate\Database\Seeder;

class BootcampCategorySeeder extends Seeder
{
    public function run(): void
    {
        BootcampCategory::factory()->count(8)->create();
    }
}
