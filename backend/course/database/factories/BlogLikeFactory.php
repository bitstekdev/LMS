<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogLikeFactory extends Factory
{
    protected $model = BlogLike::class;

    public function definition(): array
    {
        return [
            'blog_id' => Blog::inRandomOrder()->first() ?? Blog::factory(),
            'user_id' => User::inRandomOrder()->first() ?? User::factory(),
        ];
    }
}
