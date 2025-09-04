<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'parent_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'icon' => 'icon-default.png',
            'sort' => fake()->numberBetween(1, 10),
            'status' => fake()->boolean(),
            'keywords' => fake()->words(5, true),
            'description' => fake()->paragraph(),
            'thumbnail' => 'uploads/categories/thumb.png',
            'category_logo' => 'uploads/categories/logo.png',
        ];
    }
}
