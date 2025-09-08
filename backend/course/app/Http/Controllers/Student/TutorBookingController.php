<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\OfflinePayment;
use App\Models\TutorBooking;
use App\Models\TutorReview;
use App\Models\TutorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TutorBookingController extends Controller
{
    public function my_bookings()
    {
        $todayStart = strtotime('today');

        $page_data['my_bookings'] = TutorBooking::where('student_id', auth('web')->id())
            ->where('start_time', '>=', $todayStart)
            ->orderByDesc('id')
            ->paginate(10);

        $page_data['my_archive_bookings'] = TutorBooking::where('student_id', auth('web')->id())
            ->where('start_time', '<', $todayStart)
            ->orderByDesc('id')
            ->paginate(10);

        $view_path = 'frontend.'.get_frontend_settings('theme').'.student.my_bookings.index';

        return view($view_path, $page_data);
    }

    public function booking_invoice($id = '')
    {
        $booking = TutorBooking::find($id);
        if (! $booking) {
            Session::flash('error', get_phrase('Booking not found.'));

            return redirect()->back();
        }

        $page_data['booking'] = $booking;
        $page_data['invoice'] = random(10);

        $view_path = 'frontend.'.get_frontend_settings('theme').'.student.my_bookings.invoice';

        return view($view_path, $page_data);
    }

    public function purchase($id)
    {
        $schedule = TutorSchedule::find($id);
        if (! $schedule) {
            Session::flash('error', get_phrase('Schedule not found.'));

            return redirect()->back();
        }

        if ($schedule->tutor_id == auth('web')->id()) {
            Session::flash('error', get_phrase('You own this schedule.'));

            return redirect()->back();
        }

        if (TutorBooking::where('student_id', auth('web')->id())->where('schedule_id', $id)->exists()) {
            Session::flash('error', get_phrase('Schedule is already booked.'));

            return redirect()->back();
        }

        if (OfflinePayment::where([
            'user_id' => auth('web')->id(),
            'items' => $schedule->id,
            'item_type' => 'tutor_booking',
            'status' => 0,
        ])->exists()) {
            Session::flash('warning', get_phrase('Your request is in process.'));

            return redirect()->back();
        }

        $price = optional(optional($schedule->schedule_to_tutorCanTeach)->price) ?? 0;

        $payment_details = [
            'items' => [
                [
                    'id' => $schedule->id,
                    'title' => optional($schedule->schedule_to_tutorCategory)->name,
                    'subtitle' => optional($schedule->schedule_to_tutorSubjects)->name,
                    'price' => $price,
                    'discount_price' => '',
                ],
            ],
            'custom_field' => [
                'item_type' => 'tutor_booking',
                'pay_for' => get_phrase('Tutor Schedule Booking'),
            ],
            'success_method' => [
                'model_name' => 'TutorBooking',
                'function_name' => 'purchase_schedule',
            ],
            'payable_amount' => round($price, 2),
            'tax' => 0,
            'cancel_url' => route('tutor_schedule', [$schedule->tutor_id, slugify(optional($schedule->schedule_to_tutor)->name)]),
            'success_url' => route('payment.success', ''),
        ];

        Session::put(['payment_details' => $payment_details]);

        return redirect()->route('payment');
    }

    public function join_class($booking_id = '')
    {
        $current_time = time();
        $extended_time = $current_time + (60 * 15);

        $booking = TutorBooking::where('id', $booking_id)
            ->where('start_time', '<', $extended_time)
            ->where('student_id', auth('web')->id())
            ->first();

        if (! $booking) {
            Session::flash('error', get_phrase('You can join the class 15 minutes before the start or session not found.'));

            return redirect()->route('my_bookings', ['tab' => 'live-upcoming']);
        }

        if ($current_time > $booking->end_time) {
            Session::flash('error', get_phrase('Time up! Session is over.'));

            return redirect()->route('my_bookings', ['tab' => 'live-upcoming']);
        }

        if (empty($booking->joining_data)) {
            $subjectName = optional(optional($booking->booking_to_schedule)->schedule_to_tutorSubjects)->name;
            $joining_info = $this->create_zoom_meeting($subjectName, $booking->start_time);

            $meeting_info = json_decode($joining_info, true);

            if (isset($meeting_info['code'])) {
                return redirect()->back()->with('error', get_phrase($meeting_info['message']));
            }

            $booking->update(['joining_data' => $joining_info]);
            $booking->joining_data = $joining_info;
        }

        if (get_settings('zoom_web_sdk') === 'active') {
            return view('frontend.'.get_frontend_settings('theme').'.student.my_bookings.join_tution', [
                'booking' => $booking,
                'user' => get_user_info($booking->student_id),
                'is_host' => 0,
            ]);
        }

        $meeting_info = json_decode($booking->joining_data, true);

        return redirect($meeting_info['start_url'] ?? '/');
    }

    public function create_zoom_meeting($topic, $date_and_time)
    {
        $token = $this->create_zoom_token();
        if (! $token) {
            return json_encode(['code' => 'error', 'message' => 'Zoom token creation failed.']);
        }

        $meetingData = [
            'topic' => $topic,
            'type' => 2,
            'start_time' => date('Y-m-d\TH:i:s', strtotime($date_and_time)),
            'duration' => 60,
            'timezone' => get_settings('timezone'),
            'settings' => [
                'approval_type' => 2,
                'join_before_host' => true,
                'jbh_time' => 0,
            ],
        ];

        $headers = [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.zoom.us/v2/users/me/meetings');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meetingData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function create_zoom_token()
    {
        $clientId = get_settings('zoom_client_id');
        $clientSecret = get_settings('zoom_client_secret');
        $accountId = get_settings('zoom_account_id');

        $authHeader = 'Basic '.base64_encode($clientId.':'.$clientSecret);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id='.$accountId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: '.$authHeader,
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $oauthResponse = json_decode($response, true);

        return $oauthResponse['access_token'] ?? '';
    }

    public function tutor_review(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'tutor_id' => 'required|integer|exists:users,id',
        ]);

        $studentId = Auth::id();
        $tutorId = $request->input('tutor_id');

        $existingReview = TutorReview::where('tutor_id', $tutorId)->where('student_id', $studentId)->first();

        if ($existingReview) {
            $existingReview->update([
                'rating' => $request->input('rating'),
                'review' => $request->input('review'),
            ]);
        } else {
            TutorReview::create([
                'tutor_id' => $tutorId,
                'student_id' => $studentId,
                'rating' => $request->input('rating'),
                'review' => $request->input('review'),
            ]);
        }

        return redirect()->back()->with('success', get_phrase('Review submitted successfully.'));
    }
}
