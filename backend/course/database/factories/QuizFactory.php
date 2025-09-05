<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'section_id' => Section::inRandomOrder()->first()->id ?? Section::factory(),
            'title' => $this->faker->sentence,
            'duration' => '10:00',
            'total_mark' => 100,
            'pass_mark' => 70,
        ];
    }

    public function withQuestions(int $count = 5): self
    {
        return $this->has(Question::factory()->count($count), 'questions');
    }
}
