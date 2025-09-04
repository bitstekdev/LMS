<?php

namespace Database\Factories;

use App\Models\Bootcamp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BootcampModule>
 */
class BootcampModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bootcamp_id' => Bootcamp::inRandomOrder()->first()->id ?? Bootcamp::factory(),
            'title' => fake()->sentence(3),
            'publish_date' => now()->timestamp,
            'expiry_date' => now()->addWeeks(4)->timestamp,
            'restriction' => fake()->randomElement(['public', 'private']),
            'sort' => fake()->numberBetween(1, 10),
        ];
    }
}
