<?php

namespace Database\Factories;

use App\Models\TutorBooking;
use App\Models\TutorSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TutorBooking>
 */
class TutorBookingFactory extends Factory
{
    protected $model = TutorBooking::class;

    public function definition(): array
    {
        $tutor = User::where('role', 'instructor')->first();
        $student = User::where('role', 'student')->first();

        return [
            'invoice' => fake()->word,
            'schedule_id' => TutorSchedule::inRandomOrder()->first()->id ?? TutorSchedule::factory(),
            'student_id' => $student ? $student->id : null,
            'tutor_id' => $tutor ? $tutor->id : null,
            'start_time' => fake()->word,
            'end_time' => fake()->word,
            'joining_data' => fake()->word,
            'price' => fake()->word,
            'admin_revenue' => fake()->word,
            'instructor_revenue' => fake()->word,
            'tax' => fake()->word,
            'payment_method' => fake()->word,
            'payment_details' => fake()->word,
        ];
    }
}
