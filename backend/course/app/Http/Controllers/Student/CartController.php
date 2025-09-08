<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('web')->user();
        $discount = 0;
        $coupon = null;

        // Handle coupon logic
        if ($request->has('coupon')) {
            $code = $request->query('coupon');
            $coupon = Coupon::where('code', $code)->first();

            if (! $coupon) {
                return back()->with('error', get_phrase('This coupon is not valid.'));
            }

            if ($coupon->status && (time() >= $coupon->expiry)) {
                return back()->with('error', get_phrase('Ops! coupon is expired.'));
            }

            $discount = $coupon->discount;
        }

        // Get cart items with course data
        $cart_items = $user->cartItems()
            ->with('course')
            ->get()
            ->map(function ($item) {
                return [
                    'cart_id' => $item->id,
                    'course' => $item->course,
                ];
            });

        $view = 'frontend.'.get_frontend_settings('theme').'.student.cart.index';

        return view($view, [
            'cart_items' => $cart_items,
            'discount' => $discount,
            'coupon' => $coupon,
        ]);
    }

    public function store($id)
    {
        $user = auth('web')->user();

        // Cannot add own course
        if (Course::where('id', $id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', get_phrase('Ops! You own this course.'));
        }

        // Already enrolled check
        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $id)
            ->where(function ($query) {
                $query->where('expiry_date', '>', now())->orWhereNull('expiry_date');
            })
            ->exists();

        if ($alreadyEnrolled) {
            return back()->with('error', get_phrase('You already purchased the course.'));
        }

        // Add to cart if not already there
        CartItem::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $id,
        ]);

        return back()->with('success', get_phrase('Item added to the cart.'));
    }

    public function delete($id)
    {
        $user = auth('web')->user();

        $deleted = CartItem::where('course_id', $id)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted) {
            return back()->with('success', get_phrase('Item removed from cart.'));
        }

        return back()->with('error', get_phrase('Data not found.'));
    }
}
