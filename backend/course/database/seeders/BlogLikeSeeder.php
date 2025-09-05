<?php

namespace Database\Seeders;

use App\Models\BlogLike;
use Illuminate\Database\Seeder;

class BlogLikeSeeder extends Seeder
{
    public function run(): void
    {
        BlogLike::factory()->count(10)->create();
    }
}
