<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Allow optional ?year=YYYY; default to current year
        $year = (int) ($request->input('year') ?: now()->year);

        // Build an array of 12 months of admin revenue, efficiently via SUM
        $monthly_amount = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthly_amount[] = (float) PaymentHistory::query()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('admin_revenue');
        }

        return view('admin.dashboard.index', [
            'monthly_amount' => $monthly_amount,
            'year' => $year,
        ]);
    }
}
