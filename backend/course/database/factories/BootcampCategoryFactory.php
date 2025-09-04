<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BootcampCategory>
 */
class BootcampCategoryFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
        ];
    }
}
