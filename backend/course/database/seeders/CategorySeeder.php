<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()->count(5)->create()->each(function ($category) {
            Category::factory()->count(2)->create([
                'parent_id' => $category->id,
            ]);
        });
    }
}
