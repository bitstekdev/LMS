<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function cart_list(Request $request)
    {
        $user = $request->user();
        $items = CartItem::where('user_id', $user->id)->get();

        $courses = [];
        foreach ($items as $item) {
            $course = Course::find($item->course_id);
            if ($course) {
                $courses[] = $course;
            }
        }

        return course_data($courses);
    }

    public function toggle_cart_items(Request $request)
    {
        $user = $request->user();
        $course_id = $request->course_id;

        $exists = CartItem::where('user_id', $user->id)->where('course_id', $course_id)->first();

        if ($exists) {
            $exists->delete();

            return ['status' => 'removed'];
        } else {
            CartItem::create(['user_id' => $user->id, 'course_id' => $course_id]);

            return ['status' => 'added'];
        }
    }

    public function cart_tools(Request $request)
    {
        return [
            'course_selling_tax' => get_settings('course_selling_tax'),
            'currency_position' => get_settings('currency_position'),
            'currency_symbol' => DB::table('currencies')->where('code', get_settings('system_currency'))->value('symbol'),
        ];
    }
}
