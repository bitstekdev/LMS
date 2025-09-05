<?php

namespace Database\Seeders;

use App\Models\TeamPackageMember;
use Illuminate\Database\Seeder;

class TeamPackageMemberSeeder extends Seeder
{
    public function run(): void
    {
        TeamPackageMember::factory()->count(10)->create();
    }
}
