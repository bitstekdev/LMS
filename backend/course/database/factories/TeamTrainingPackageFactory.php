<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamTrainingPackage>
 */
class TeamTrainingPackageFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);
        $start = Carbon::now()->addDays(fake()->numberBetween(-7, 7))->startOfDay();
        $end = (clone $start)->addDays(fake()->numberBetween(30, 365));

        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'price' => fake()->randomFloat(2, 0, 9999.99),
            'course_privacy' => fake()->randomElement(['publicly', 'privately', 'team']),
            'course_id' => Course::query()->inRandomOrder()->value('id') ?? Course::factory(),
            'allocation' => fake()->numberBetween(5, 200),
            'expiry_type' => fake()->randomElement(['date_range', 'fixed_days', 'lifetime']),
            'start_date' => $start->timestamp,          // int
            'expiry_date' => $end->timestamp,            // int (nullable in state below)
            'features' => json_encode(fake()->randomElements([
                'All courses',
                'Live classes',
                'Certificates',
                'Team analytics',
                'Priority support',
                'Custom branding',
            ], fake()->numberBetween(3, 6))),
            'thumbnail' => fake()->imageUrl(960, 540, 'business', true, 'package'),
            'pricing_type' => fake()->randomElement([1, 2]), // e.g. 1=one-time, 2=subscription
            'status' => fake()->randomElement([0, 1]), // 0=inactive, 1=active
        ];
    }

    /** Lifetime access (no expiry). */
    public function lifetime(): static
    {
        return $this->state(fn () => [
            'expiry_type' => 'lifetime',
            'start_date' => Carbon::now()->startOfDay()->timestamp,
            'expiry_date' => null,
        ]);
    }

    /** Free package. */
    public function free(): static
    {
        return $this->state(fn () => ['price' => 0.00]);
    }

    /** Force active status. */
    public function active(): static
    {
        return $this->state(fn () => ['status' => 1]);
    }
}
