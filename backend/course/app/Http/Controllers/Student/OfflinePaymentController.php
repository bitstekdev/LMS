<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\OfflinePayment;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OfflinePaymentController extends Controller
{
    public function store(Request $request)
    {
        $payment_details = Session::get('payment_details');

        if (! $payment_details || empty($payment_details['items'])) {
            return redirect()->back()->with('error', get_phrase('Payment session expired. Please try again.'));
        }

        $item_id_arr = collect($payment_details['items'])->pluck('id')->toArray();

        // Validation
        $validator = Validator::make($request->all(), [
            'doc' => 'required|mimes:jpeg,jpg,pdf,txt,png,docx,doc|max:3072',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // File Upload
        $file = $request->file('doc');
        $file_name = Str::random(20).'.'.$file->getClientOriginalExtension();
        $path = 'uploads/offline_payment/'.slugify(auth('web')->user()->name).'/'.$file_name;

        app(FileUploaderService::class)->upload($file, $path, null, null, 300);

        // Create payment record
        $offline_payment = [
            'user_id' => auth('web')->user()->id,
            'item_type' => $request->item_type,
            'items' => json_encode($item_id_arr),
            'tax' => $payment_details['tax'] ?? 0,
            'total_amount' => $payment_details['payable_amount'] ?? 0,
            'doc' => $path,
            'coupon' => $payment_details['coupon'] ?? null,
        ];

        OfflinePayment::create($offline_payment);

        // Handle post-payment logic
        switch ($request->item_type) {
            case 'course':
                CartItem::whereIn('course_id', $item_id_arr)
                    ->where('user_id', auth('web')->user()->id)
                    ->delete();
                $redirectRoute = 'purchase.history';
                break;

            case 'bootcamp':
                $redirectRoute = 'bootcamps';
                break;

            case 'package':
                $redirectRoute = 'team.packages';
                break;

            case 'tutor_booking':
                $redirectRoute = 'tutor_list';
                break;

            default:
                $redirectRoute = 'home';
                break;
        }

        Session::flash('success', get_phrase('The payment will be completed once the admin reviews and approves it.'));

        return redirect()->route($redirectRoute);
    }
}
