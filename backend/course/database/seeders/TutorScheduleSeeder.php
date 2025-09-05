<?php

namespace Database\Seeders;

use App\Models\TutorSchedule;
use Illuminate\Database\Seeder;

class TutorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        TutorSchedule::factory()->count(10)->create();
    }
}
