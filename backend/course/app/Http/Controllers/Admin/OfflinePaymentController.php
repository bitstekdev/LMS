<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bootcamp;
use App\Models\BootcampPurchase;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\OfflinePayment;
use App\Models\PaymentHistory;
use App\Models\TeamPackagePurchase;
use App\Models\TeamTrainingPackage;
use App\Models\TutorBooking;
use App\Models\TutorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class OfflinePaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = OfflinePayment::orderBy('id', 'DESC');

        if ($request->status === 'approved') {
            $payments->where('status', 1);
        } elseif ($request->status === 'suspended') {
            $payments->where('status', 2);
        } elseif ($request->status === 'pending') {
            $payments->where(function ($query) {
                $query->where('status', 0)->orWhereNull('status');
            });
        }

        $page_data['payments'] = $payments->paginate(10);

        return view('admin.offline_payments.index', $page_data);
    }

    public function download_doc($id)
    {
        $payment = OfflinePayment::find($id);
        if (! $payment || empty($payment->doc)) {
            return redirect()->back()->with('error', get_phrase('Document not found.'));
        }

        $filePath = public_path($payment->doc);
        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', get_phrase('File not found.'));
        }

        return Response::download($filePath);
    }

    public function accept_payment($id)
    {
        $payment = OfflinePayment::where('id', $id)->where('status', 0)->first();

        if (! $payment) {
            return redirect()->back()->with('error', get_phrase('Payment not found or already processed.'));
        }

        $userId = $payment->user_id;
        $items = json_decode($payment->items, true);
        $coupon = $payment->coupon ? Coupon::where('code', $payment->coupon)->first() : null;

        switch ($payment->item_type) {
            case 'course':
                $this->processCoursePayment($items, $userId, $payment, $coupon);
                break;

            case 'bootcamp':
                $this->processBootcampPayment($items, $userId);
                break;

            case 'package':
                $this->processPackagePayment($items, $userId);
                break;

            case 'tutor_booking':
                $this->processTutorBookingPayment($items, $payment);
                break;

            default:
                return redirect()->back()->with('error', get_phrase('Invalid item type.'));
        }

        $payment->update(['status' => 1]);

        return redirect()->route('admin.offline.payments')->with('success', get_phrase('Payment has been accepted.'));
    }

    private function processCoursePayment($courseIds, $userId, $payment, $coupon = null)
    {
        foreach ($courseIds as $courseId) {
            $course = Course::find($courseId);
            if (! $course) {
                continue;
            }

            $amount = $course->discount_flag ? $course->discounted_price : $course->price;
            $discount = $coupon ? ($amount * ($coupon->discount / 100)) : 0;
            $finalAmount = $amount - $discount;
            $tax = (get_settings('course_selling_tax') / 100) * $finalAmount;

            $creatorRole = get_course_creator_id($course->id)->role;
            $instructorRevenue = $creatorRole === 'admin' ? 0 : $finalAmount * (get_settings('instructor_revenue') / 100);
            $adminRevenue = $finalAmount - $instructorRevenue;

            $paymentData = [
                'invoice' => Str::random(20),
                'user_id' => $userId,
                'course_id' => $course->id,
                'payment_type' => 'offline',
                'coupon' => $coupon?->code,
                'amount' => $finalAmount,
                'tax' => $tax,
                'admin_revenue' => $adminRevenue,
                'instructor_revenue' => $instructorRevenue,
            ];

            if (PaymentHistory::insert($paymentData)) {
                $enroll = [
                    'user_id' => $userId,
                    'course_id' => $course->id,
                    'enrollment_type' => 'paid',
                    'entry_date' => time(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'expiry_date' => $course->expiry_period > 0 ? strtotime("+{$course->expiry_period} months") : null,
                ];

                Enrollment::insert($enroll);
            }
        }
    }

    private function processBootcampPayment($bootcampIds, $userId)
    {
        $bootcamps = Bootcamp::whereIn('id', $bootcampIds)->get();

        foreach ($bootcamps as $bootcamp) {
            $price = $bootcamp->discount_flag ? $bootcamp->discounted_price : $bootcamp->price;

            $bootcampPayment = [
                'invoice' => '#'.Str::random(20),
                'user_id' => $userId,
                'bootcamp_id' => $bootcamp->id,
                'price' => $price,
                'tax' => 0,
                'payment_method' => 'offline',
                'status' => 1,
            ];

            BootcampPurchase::insert($bootcampPayment);
        }
    }

    private function processPackagePayment($packageIds, $userId)
    {
        $packages = TeamTrainingPackage::whereIn('id', $packageIds)->get();

        foreach ($packages as $package) {
            $packagePayment = [
                'invoice' => '#'.Str::random(20),
                'user_id' => $userId,
                'package_id' => $package->id,
                'price' => $package->price,
                'tax' => 0,
                'payment_method' => 'offline',
                'status' => 1,
            ];

            TeamPackagePurchase::insert($packagePayment);
        }
    }

    private function processTutorBookingPayment($scheduleIds, $payment)
    {
        $schedules = TutorSchedule::whereIn('id', $scheduleIds)->get();

        foreach ($schedules as $schedule) {
            $tutor = get_user_info($schedule->tutor_id);
            $instructorRevenue = $tutor->role === 'admin' ? 0 : $payment->total_amount * (get_settings('instructor_revenue') / 100);
            $adminRevenue = $payment->total_amount - $instructorRevenue;

            $booking = [
                'invoice' => '#'.Str::random(20),
                'student_id' => $payment->user_id,
                'schedule_id' => $schedule->id,
                'tutor_id' => $schedule->tutor_id,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'price' => $payment->total_amount,
                'tax' => $payment->tax,
                'payment_method' => 'offline',
                'admin_revenue' => $adminRevenue,
                'instructor_revenue' => $instructorRevenue,
            ];

            TutorBooking::insert($booking);
        }
    }

    public function decline_payment($id)
    {
        OfflinePayment::where('id', $id)->update(['status' => 2]);

        return redirect()->route('admin.offline.payments')->with('success', get_phrase('Payment has been suspended.'));
    }

    public function delete_payment($id)
    {
        OfflinePayment::where('id', $id)->delete();

        return redirect()->route('admin.offline.payments')->with('success', get_phrase('Payment record deleted successfully.'));
    }
}
