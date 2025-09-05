<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first() ?? User::factory(),
            'category_id' => BlogCategory::inRandomOrder()->first() ?? BlogCategory::factory(),
            'title' => fake()->sentence(3),
            'slug' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'thumbnail' => fake()->word(),
            'banner' => fake()->word(),
            'keywords' => fake()->paragraph(),
            'is_popular' => fake()->word(),
            'status' => fake()->word(),
        ];
    }
}
