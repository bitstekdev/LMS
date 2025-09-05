<?php

namespace Database\Factories;

use App\Models\TeamTrainingPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamPackageMember>
 */
class TeamPackageMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'leader_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'team_package_id' => TeamTrainingPackage::inRandomOrder()->first()?->id ?? TeamTrainingPackage::factory(),
            'member_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
        ];
    }
}
