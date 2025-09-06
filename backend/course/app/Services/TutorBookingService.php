<?php

namespace App\Services;

use App\Models\TutorBooking;
use App\Models\TutorSchedule;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TutorBookingService
{
    public function purchase(string $identifier)
    {
        $paymentDetails = session('payment_details');
        $removeSessionItems = ['payment_details'];

        $transactionData = null;
        if (Session::has('keys')) {
            $transactionData = json_encode(Session::pull('keys'));
            $removeSessionItems[] = 'keys';
        }

        if (Session::has('session_id')) {
            $transactionData = Session::pull('session_id');
            $removeSessionItems[] = 'session_id';
        }

        $scheduleId = $paymentDetails['items'][0]['id'];
        $schedule = TutorSchedule::findOrFail($scheduleId);

        $price = $paymentDetails['payable_amount'];
        $adminRevenue = $instructorRevenue = 0;

        if (get_user_info($schedule->tutor_id)->role === 'admin') {
            $adminRevenue = $price;
        } else {
            $instructorRevenue = $price * (get_settings('instructor_revenue') / 100);
            $adminRevenue = $price - $instructorRevenue;
        }

        TutorBooking::create([
            'invoice' => '#'.Str::random(20),
            'student_id' => auth('web')->id(),
            'schedule_id' => $scheduleId,
            'tutor_id' => $schedule->tutor_id,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'price' => $price,
            'tax' => $paymentDetails['tax'],
            'payment_method' => $identifier,
            'payment_details' => $transactionData,
            'admin_revenue' => $adminRevenue,
            'instructor_revenue' => $instructorRevenue,
        ]);

        Session::forget($removeSessionItems);
        Session::flash('success', get_phrase('Tutor schedule booked successfully.'));

        return redirect()->route('my_bookings', ['tab' => 'live-upcoming']);
    }
}
