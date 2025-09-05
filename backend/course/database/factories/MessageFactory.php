<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'receiver_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'message' => $this->faker->sentence,
            'read' => $this->faker->boolean(50),
        ];
    }
}
