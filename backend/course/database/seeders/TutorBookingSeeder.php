<?php

namespace Database\Seeders;

use App\Models\TutorBooking;
use Illuminate\Database\Seeder;

class TutorBookingSeeder extends Seeder
{
    public function run(): void
    {
        TutorBooking::factory()->count(10)->create();
    }
}
