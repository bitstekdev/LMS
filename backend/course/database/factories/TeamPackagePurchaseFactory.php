<?php

namespace Database\Factories;

use App\Models\TeamTrainingPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamPackagePurchase>
 */
class TeamPackagePurchaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice' => fake()->uuid(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'package_id' => TeamTrainingPackage::inRandomOrder()->first()?->id ?? TeamTrainingPackage::factory(),
            'price' => fake()->randomFloat(2, 100, 1000),
            'admin_revenue' => fake()->randomFloat(2, 20, 100),
            'instructor_revenue' => fake()->randomFloat(2, 20, 100),
            'tax' => fake()->randomFloat(2, 5, 20),
            'payment_method' => fake()->randomElement(['stripe', 'paypal']),
            'payment_details' => fake()->text(),
            'status' => fake()->numberBetween(0, 1),
        ];
    }
}
