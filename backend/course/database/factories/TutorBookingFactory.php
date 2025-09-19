<?php

namespace Database\Factories;

use App\Models\TutorBooking;
use App\Models\TutorSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TutorBooking>
 */
class TutorBookingFactory extends Factory
{
    protected $model = TutorBooking::class;

    public function definition(): array
    {
        $tutor = User::where('role', 'instructor')->inRandomOrder()->first() ?? User::factory()->create(['role' => 'instructor']);
        $student = User::where('role', 'student')->inRandomOrder()->first() ?? User::factory()->create(['role' => 'student']);

        // Generate a random start & end time
        $start = fake()->dateTimeBetween('+1 days', '+7 days');
        $end = Carbon::instance($start)->addHours(2); // 2-hour session

        return [
            'invoice' => strtoupper(fake()->bothify('INV-####')),
            'schedule_id' => TutorSchedule::inRandomOrder()->first()->id ?? TutorSchedule::factory(),
            'student_id' => $student->id,
            'tutor_id' => $tutor->id,
            'start_time' => $start,
            'end_time' => $end,
            'joining_data' => fake()->url,
            'price' => fake()->randomFloat(2, 20, 200),
            'admin_revenue' => fake()->randomFloat(2, 5, 50),
            'instructor_revenue' => fake()->randomFloat(2, 10, 150),
            'tax' => fake()->randomFloat(2, 0, 20),
            'payment_method' => fake()->randomElement(['stripe', 'paypal', 'bank_transfer']),
            'payment_details' => fake()->text(100),
        ];
    }
}
