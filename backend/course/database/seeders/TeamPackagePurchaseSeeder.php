<?php

namespace Database\Seeders;

use App\Models\TeamPackagePurchase;
use Illuminate\Database\Seeder;

class TeamPackagePurchaseSeeder extends Seeder
{
    public function run(): void
    {
        TeamPackagePurchase::factory()->count(10)->create();
    }
}
