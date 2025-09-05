<?php

namespace Database\Seeders;

use App\Models\BlogLike;
use Illuminate\Database\Seeder;

class BlogLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BlogLike::factory()->count(10)->create();
    }
}
