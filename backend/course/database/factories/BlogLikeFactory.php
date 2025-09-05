<?php

namespace Database\Factories;

use App\Models\BlogLike;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogLikeFactory extends Factory
{
    protected $model = BlogLike::class;

    public function definition(): array
    {
        return [
            'blog_id' => 1,
            'user_id' => 1,
        ];
    }
}
