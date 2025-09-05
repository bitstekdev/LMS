<?php

namespace Database\Seeders;

use App\Models\TutorCanTeach;
use Illuminate\Database\Seeder;

class TutorCanTeachSeeder extends Seeder
{
    public function run(): void
    {
        TutorCanTeach::factory()->count(10)->create();
    }
}
