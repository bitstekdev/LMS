<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $monthlyAmount = [];

        // Loop through each month (1 to 12)
        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::createFromDate(now()->year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->endOfDay();

            // Get total instructor revenue for the month
            $amount = PaymentHistory::whereBetween('created_at', [$start, $end])
                ->sum('instructor_revenue');

            $monthlyAmount[] = $amount;
        }

        return view('instructor.dashboard.index', [
            'monthly_amount' => $monthlyAmount,
        ]);
    }
}
