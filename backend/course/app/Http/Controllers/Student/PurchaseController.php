<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PurchaseController extends Controller
{
    public function purchase_history()
    {
        $payments = auth('web')->user()->payments()
            ->with('course:id,title', 'user:id,name')
            ->latest('id')
            ->paginate(10);

        $view = 'frontend.'.get_frontend_settings('theme').'.student.purchase_history.index';

        return view($view, ['payments' => $payments]);
    }

    public function invoice($id)
    {
        $payment = PaymentHistory::with(['course:id,title', 'user:id,name'])
            ->where('id', $id)
            ->first();

        if (! $payment) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        $view = 'frontend.'.get_frontend_settings('theme').'.student.purchase_history.invoice';

        return view($view, ['invoice' => $payment]);
    }

    public function purchase_course($course_id)
    {
        $user = auth('web')->user();

        $course = Course::find($course_id);

        if (! $course) {
            return back()->with('error', get_phrase('Data not found.'));
        }

        if ($course->user_id === $user->id) {
            return back()->with('error', get_phrase('Ops! You own this course.'));
        }

        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course_id)
            ->where(function ($q) {
                $q->where('expiry_date', '>', now())->orWhereNull('expiry_date');
            })->exists();

        if ($alreadyEnrolled) {
            return back()->with('error', get_phrase('You already enrolled in this course.'));
        }

        if (! $course->is_paid) {
            $expiry = $course->expiry_period > 0 ? now()->addDays($course->expiry_period * 30)->timestamp : null;

            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course_id,
                'enrollment_type' => 'free',
                'entry_date' => now()->timestamp,
                'expiry_date' => $expiry,
            ]);

            return redirect()->route('my.courses');
        }

        CartItem::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course_id,
        ]);

        return redirect()->route('cart');
    }

    public function payout(Request $request)
    {
        $item_ids = json_decode($request->items);
        $user = auth('web')->user();

        $gifted_user_id = null;
        $courses_to_purchase = $item_ids;

        // If gifting a course
        if ($request->filled('gifted_user_email')) {
            $gifted_user = User::where('email', $request->gifted_user_email)->where('role', '!=', 'admin')->first();

            if (! $gifted_user) {
                return back()->with('error', get_phrase("User email doesn't exist."));
            }

            $gifted_user_id = $gifted_user->id;

            // Filter out already enrolled courses for the gifted user
            $courses_to_purchase = array_filter($item_ids, function ($course_id) use ($gifted_user_id) {
                return ! Enrollment::where('course_id', $course_id)
                    ->where('user_id', $gifted_user_id)
                    ->exists();
            });

            if (empty($courses_to_purchase)) {
                return back()->with('error', get_phrase('User already enrolled in all selected courses.'));
            }
        }

        $selected_courses = Course::whereIn('id', $courses_to_purchase)->get();

        $items = $selected_courses->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'subtitle' => '',
                'price' => $course->price,
                'discount_price' => $course->discount_flag ? $course->discounted_price : 0,
            ];
        })->toArray();

        $payment_details = [
            'items' => $items,
            'custom_field' => [
                'item_type' => 'course',
                'pay_for' => 'course payment',
                'user_id' => $user->id,
                'user_photo' => $user->photo,
                'cart_id' => $item_ids,
                'coupon_discount' => $request->coupon_discount ?? 0,
                'gifted_user_id' => $gifted_user_id ?? '',
            ],
            'success_method' => [
                'model_name' => 'PurchaseCourse',
                'function_name' => 'purchase_course',
            ],
            'tax' => round($request->tax, 2),
            'coupon' => $request->coupon_code,
            'payable_amount' => round($request->payable, 2),
            'cancel_url' => route('cart'),
            'success_url' => route('payment.success', ''),
        ];

        Session::put(['payment_details' => $payment_details]);

        return redirect()->route('payment');
    }
}
