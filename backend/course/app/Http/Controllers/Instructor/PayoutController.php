<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PayoutController extends Controller
{
    public function index()
    {
        // Default to current month
        $startDate = strtotime('first day of this month');
        $endDate = strtotime('last day of this month');

        // Check for custom date range filter
        if (request()->has('eDateRange')) {
            $dateRange = explode('-', urldecode(request()->query('eDateRange')));
            $startDate = strtotime(trim($dateRange[0]).' 00:00:00');
            $endDate = strtotime(trim($dateRange[1]).' 23:59:59');
        }

        $userId = auth('web')->id();

        // Payouts within date range
        $payoutReports = Payout::where('user_id', $userId)
            ->whereBetween('created_at', [
                date('Y-m-d H:i:s', $startDate),
                date('Y-m-d H:i:s', $endDate),
            ])
            ->latest('id')
            ->paginate(10)
            ->appends(['eDateRange' => request()->query('eDateRange')]);

        $page_data = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payout_reports' => $payoutReports,
            'payout_request' => Payout::where('user_id', $userId)->where('status', 0)->first(),
            'total_payout' => instructor_total_payout(),
            'balance' => instructor_available_balance(),
        ];

        return view('instructor.payout_report.index', $page_data);
    }

    public function store(Request $request)
    {
        $userId = auth('web')->id();

        // Prevent duplicate request
        if (Payout::where('user_id', $userId)->where('status', 0)->exists()) {
            Session::flash('error', get_phrase('Your request is in process.'));

            return redirect()->back();
        }

        $totalIncome = instructor_total_revenue();
        $totalPayout = instructor_total_payout();
        $balanceRemaining = $totalIncome - $totalPayout;

        // Validate amount
        if ($request->amount < 1 || $request->amount > $balanceRemaining) {
            Session::flash('error', get_phrase('You do not have sufficient balance.'));

            return redirect()->back();
        }

        // Store new payout request
        Payout::create([
            'user_id' => $userId,
            'amount' => $request->amount,
        ]);

        Session::flash('success', get_phrase('Your request has been submitted.'));

        return redirect()->back();
    }

    public function delete($id)
    {
        $userId = auth('web')->id();

        $payout = Payout::where('id', $id)->where('user_id', $userId)->first();

        if (! $payout) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $payout->delete();

        Session::flash('success', get_phrase('Your request has been deleted.'));

        return redirect()->back();
    }
}
