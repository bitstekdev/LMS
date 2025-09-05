<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sender_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'receiver_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'message' => fake()->sentence,
            'read' => fake()->boolean(50),
        ];
    }
}
