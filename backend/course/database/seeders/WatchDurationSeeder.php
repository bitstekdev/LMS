<?php

namespace Database\Seeders;

use App\Models\WatchDuration;
use Illuminate\Database\Seeder;

class WatchDurationSeeder extends Seeder
{
    public function run(): void
    {
        WatchDuration::factory()->count(10)->create();
    }
}
