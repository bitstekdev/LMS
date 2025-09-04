<?php

namespace Database\Factories;

use App\Models\BootcampModule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BootcampLiveClass>
 */
class BootcampLiveClassFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'module_id' => BootcampModule::inRandomOrder()->first()->id ?? BootcampModule::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'start_time' => now()->timestamp,
            'end_time' => now()->addHour()->timestamp,
            'sort' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['scheduled', 'completed']),
            'provider' => fake()->randomElement(['zoom', 'meet', 'teams']),
            'joining_data' => json_encode(['url' => 'https://meet.fake/class']),
            'force_stop' => false,
        ];
    }
}
