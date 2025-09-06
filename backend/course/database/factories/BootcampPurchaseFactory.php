<?php

namespace Database\Factories;

use App\Models\Bootcamp;
use App\Models\BootcampPurchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BootcampPurchaseFactory extends Factory
{
    protected $model = BootcampPurchase::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // pick existing user/bootcamp if available, otherwise create
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $bootcamp = Bootcamp::inRandomOrder()->first() ?? Bootcamp::factory()->create();

        $price = $this->faker->randomFloat(2, 10, 499);
        $tax = round($price * 0.1, 2); // 10% tax example
        $admin = round($price * 0.2, 2);
        $inst = round($price - $admin, 2);

        return [
            'invoice' => Str::upper(Str::random(3)).'-'.$this->faker->unique()->numerify('########'),
            'user_id' => $user->id,
            'bootcamp_id' => $bootcamp->id,
            'price' => $price,
            'tax' => $tax,
            'payment_method' => $this->faker->randomElement(['stripe', 'paypal', 'bank', 'manual']),
            'payment_details' => json_encode(['tx' => Str::uuid()]),
            'status' => $this->faker->randomElement([0, 1]), // pending/paid
            'admin_revenue' => $admin,
            'instructor_revenue' => $inst,
        ];
    }

    /**
     * Mark as paid.
     */
    public function paid(): self
    {
        return $this->state(fn () => ['status' => 1]);
    }

    /**
     * Mark as pending.
     */
    public function pending(): self
    {
        return $this->state(fn () => ['status' => 0]);
    }
}
