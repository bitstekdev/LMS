<?php

namespace Database\Seeders;

use App\Models\WatchHistory;
use Illuminate\Database\Seeder;

class WatchHistorySeeder extends Seeder
{
    public function run(): void
    {
        WatchHistory::factory()->count(10)->create();
    }
}
