<?php

namespace Database\Factories;

use App\Models\Bootcamp;
use App\Models\BootcampCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bootcamp>
 */
class BootcampFactory extends Factory
{
    protected $model = Bootcamp::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'category_id' => BootcampCategory::inRandomOrder()->first()->id ?? BootcampCategory::factory(),
            'description' => fake()->paragraph(3),
            'short_description' => fake()->sentence(),
            'is_paid' => fake()->boolean(),
            'price' => fake()->randomFloat(2, 50, 200),
            'discount_flag' => fake()->boolean(),
            'discounted_price' => fake()->randomFloat(2, 20, 49),
            'publish_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'thumbnail' => 'uploads/bootcamps/thumb.jpg',
            'faqs' => json_encode([
                ['question' => 'What is this bootcamp about?', 'answer' => 'It covers everything from basics to advanced.'],
            ]),
            'requirements' => json_encode([
                'Basic knowledge of computers',
                'Internet connection',
            ]),
            'outcomes' => json_encode([
                'You’ll be able to build projects',
                'Understand core concepts',
            ]),
            'meta_keywords' => 'bootcamp, learn, programming',
            'meta_description' => 'A complete bootcamp for learning.',
            'status' => 1,
        ];
    }
}
