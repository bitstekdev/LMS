<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::where('user_id', auth('web')->id());

        if ($request->filled('search')) {
            $query->where('code', 'like', '%'.$request->query('search').'%');
        }

        $page_data['coupons'] = $query->paginate(10)->appends($request->query());

        return view('admin.coupon.index', $page_data);
    }

    public function create()
    {
        return view('admin.coupon.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:coupons,code',
            'discount' => 'required|numeric|between:1,100',
            'expiry' => 'required|date|after_or_equal:today',
            'status' => 'required|in:0,1',
        ], [
            'expiry.after_or_equal' => 'Expiry date must be today or a future date.',
            'status.in' => 'Status must be either 0 (inactive) or 1 (active).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Coupon::create([
            'code' => $request->code,
            'user_id' => auth('web')->id(),
            'discount' => $request->discount,
            'expiry' => strtotime($request->expiry),
            'status' => $request->status,
        ]);

        Session::flash('success', get_phrase('Coupon has been created successfully.'));

        return redirect()->route('admin.coupons');
    }

    public function edit($id)
    {
        $coupon = Coupon::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $coupon) {
            return redirect()->back()->with('error', get_phrase('Data not found.'));
        }

        return view('admin.coupon.edit', ['coupon_details' => $coupon]);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $coupon) {
            return redirect()->back()->with('error', get_phrase('Data not found.'));
        }

        $validator = Validator::make($request->all(), [
            'code' => "required|string|unique:coupons,code,$id",
            'discount' => 'required|numeric|between:1,100',
            'expiry' => 'required|date|after_or_equal:today',
            'status' => 'required|in:0,1',
        ], [
            'expiry.after_or_equal' => 'Expiry date must be today or a future date.',
            'status.in' => 'Status must be either 0 (inactive) or 1 (active).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $coupon->update([
            'code' => $request->code,
            'discount' => $request->discount,
            'expiry' => strtotime($request->expiry),
            'status' => $request->status,
        ]);

        Session::flash('success', get_phrase('Coupon has been updated successfully.'));

        return redirect()->route('admin.coupons');
    }

    public function delete($id)
    {
        $coupon = Coupon::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $coupon) {
            return redirect()->back()->with('error', get_phrase('Data not found.'));
        }

        $coupon->delete();

        Session::flash('success', get_phrase('Coupon has been deleted successfully.'));

        return redirect()->route('admin.coupons');
    }

    public function status($id)
    {
        $coupon = Coupon::where('id', $id)->where('user_id', auth('web')->id())->first();

        if (! $coupon) {
            return redirect()->back()->with('error', get_phrase('Data not found.'));
        }

        $coupon->status = ! $coupon->status;
        $coupon->save();

        Session::flash('success', get_phrase('Status has been updated.'));

        return redirect()->route('admin.coupons');
    }
}
