<?php

namespace Database\Seeders;

use App\Models\TeamTrainingPackage;
use Illuminate\Database\Seeder;

class TeamTrainingPackageSeeder extends Seeder
{
    public function run(): void
    {
        $randomCount = 20;
        $eachStateCnt = 3;

        // Random packages
        TeamTrainingPackage::factory()->count($randomCount)->create();

        // 3 Active (status = active)
        TeamTrainingPackage::factory()->count($eachStateCnt)->active()->create();

        // 3 Free (price = 0, pricing_type = free)
        TeamTrainingPackage::factory()->count($eachStateCnt)->free()->create();

        // 3 Lifetime (expiry_type = lifetime, no end date)
        TeamTrainingPackage::factory()->count($eachStateCnt)->lifetime()->create();

        // OPTIONAL: 3 that are Active + Free + Lifetime simultaneously
        TeamTrainingPackage::factory()
            ->count($eachStateCnt)
            ->active()
            ->free()
            ->lifetime()
            ->create();
    }
}
