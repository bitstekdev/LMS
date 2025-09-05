<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogCommentFactory extends Factory
{
    protected $model = BlogComment::class;

    public function definition(): array
    {
        return [
            'blog_id' => Blog::inRandomOrder()->first() ?? Blog::factory(),
            'user_id' => User::inRandomOrder()->first() ?? User::factory(),
            'parent_id' => BlogComment::inRandomOrder()->first()?->id ?? null,
            'check' => fake()->word(),
            'comment' => fake()->paragraph(),
            'likes' => fake()->paragraph(),
        ];
    }
}
