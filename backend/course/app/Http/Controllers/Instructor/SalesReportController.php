<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        // Set default to current month
        $startDate = strtotime('first day of this month');
        $endDate = strtotime('last day of this month');

        if ($request->has('eDateRange')) {
            // Parse date range from URL query
            $dates = explode('-', urldecode($request->query('eDateRange')));
            $startDate = strtotime(trim($dates[0]).' 00:00:00');
            $endDate = strtotime(trim($dates[1]).' 23:59:59');
        }

        // Filter payments by instructor & date range
        $query = PaymentHistory::join('courses', 'payment_histories.course_id', '=', 'courses.id')
            ->join('users', 'payment_histories.user_id', '=', 'users.id')
            ->select(
                'payment_histories.*',
                'courses.title as course_title',
                'courses.slug as course_slug',
                'courses.user_id as instructor_id',
                'users.name as student_name'
            )
            ->where('courses.user_id', auth('web')->id())
            ->whereBetween('payment_histories.created_at', [
                date('Y-m-d H:i:s', $startDate),
                date('Y-m-d H:i:s', $endDate),
            ])
            ->orderBy('payment_histories.created_at', 'desc');

        $page_data = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sales_report' => $query->paginate(10)->appends($request->query()),
        ];

        return view('instructor.sales_report.index', $page_data);
    }
}
