<?php

namespace Database\Factories;

use App\Models\BlogComment;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogCommentFactory extends Factory
{
    protected $model = BlogComment::class;

    public function definition(): array
    {
        return [
            'blog_id' => 1,
            'user_id' => 1,
            'parent_id' => 1,
            'check' => fake()->word(),
            'comment' => fake()->paragraph(),
            'likes' => fake()->paragraph(),
        ];
    }
}
