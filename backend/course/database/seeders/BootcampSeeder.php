<?php

namespace Database\Seeders;

use App\Models\Bootcamp;
use Illuminate\Database\Seeder;

class BootcampSeeder extends Seeder
{
    public function run(): void
    {
        Bootcamp::factory()->count(10)->create();
    }
}
