<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentHistory>
 */
class PaymentHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
            'payment_type' => fake()->randomElement(['stripe', 'paypal']),
            'amount' => fake()->randomFloat(2, 50, 500),
            'invoice' => fake()->uuid(),
            'date_added' => now()->timestamp,
            'last_modified' => now()->timestamp,
            'admin_revenue' => fake()->randomFloat(2, 10, 50),
            'instructor_revenue' => fake()->randomFloat(2, 20, 100),
            'tax' => fake()->randomFloat(2, 1, 20),
            'instructor_payment_status' => fake()->boolean(),
            'transaction_id' => fake()->uuid(),
            'session_id' => fake()->uuid(),
            'coupon' => null,
        ];
    }
}
