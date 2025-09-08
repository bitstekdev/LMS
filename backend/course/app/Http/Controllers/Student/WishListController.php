<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class WishListController extends Controller
{
    public function index()
    {
        $wishlist = Wishlist::join('courses', 'wishlists.course_id', '=', 'courses.id')
            ->join('users', 'courses.user_id', '=', 'users.id')
            ->select(
                'wishlists.*',
                'courses.*',
                'courses.thumbnail as course_thumbnail',
                'users.name as user_name',
                'users.photo as users_photo'
            )
            ->where('wishlists.user_id', auth('web')->id())
            ->paginate(6);

        $view_path = 'frontend.'.get_frontend_settings('theme').'.student.wishlist.index';

        return view($view_path, compact('wishlist'));
    }

    public function toggleWishItem(Request $request, $course_id = null)
    {
        if (! is_numeric($course_id) || $course_id < 1) {
            return response()->json(['error' => 'Invalid course ID.'], 400);
        }

        $wishlist = Wishlist::where('user_id', auth('web')->id())
            ->where('course_id', $course_id);

        if ($wishlist->exists()) {
            $wishlist->delete();
            $status = 'removed';

            if (! $request->ajax()) {
                Session::flash('success', get_phrase('Item removed from wishlist.'));

                return redirect()->back();
            }
        } else {
            Wishlist::create([
                'user_id' => auth('web')->id(),
                'course_id' => $course_id,
            ]);
            $status = 'added';

            if (! $request->ajax()) {
                Session::flash('success', get_phrase('Item added to wishlist.'));

                return redirect()->back();
            }
        }

        return response()->json(['toggleStatus' => $status]);
    }
}
