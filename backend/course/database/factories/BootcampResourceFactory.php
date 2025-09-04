<?php

namespace Database\Factories;

use App\Models\BootcampModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BootcampResource>
 */
class BootcampResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'module_id' => BootcampModule::inRandomOrder()->first()->id ?? BootcampModule::factory(),
            'title' => fake()->sentence(2),
            'upload_type' => fake()->randomElement(['pdf', 'video', 'doc']),
            'file' => 'uploads/resources/sample.pdf',
            'uploaded_at' => now(),
        ];
    }
}
