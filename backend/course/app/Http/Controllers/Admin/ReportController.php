<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ReportController extends Controller
{
    public function admin_revenue(Request $request)
    {
        $page_data = $this->generateReport(
            $request,
            'admin_revenue',
            'admin.report.admin_revenue'
        );

        return view('admin.report.admin_revenue', $page_data);
    }

    public function admin_revenue_filter(Request $request)
    {
        return $this->admin_revenue($request);
    }

    public function admin_revenue_delete($id)
    {
        PaymentHistory::where('id', $id)->delete();
        Session::flash('success', get_phrase('Admin revenue deleted successfully'));

        return redirect()->back();
    }

    public function instructor_revenue(Request $request)
    {
        $page_data = $this->generateReport(
            $request,
            'instructor_revenue',
            'admin.report.instructor_revenue'
        );

        return view('admin.report.instructor_revenue', $page_data);
    }

    public function instructor_revenue_delete($id)
    {
        PaymentHistory::where('id', $id)->delete();
        Session::flash('success', get_phrase('Instructor revenue deleted successfully'));

        return redirect()->back();
    }

    public function purchase_history(Request $request)
    {
        $dateRange = $this->extractDateRange($request->eDateRange);
        $reports = PaymentHistory::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->latest('id')
            ->paginate(10)
            ->appends($request->query());

        return view('admin.report.purchase_history', [
            'start_date' => strtotime($dateRange['start']),
            'end_date' => strtotime($dateRange['end']),
            'reports' => $reports,
        ]);
    }

    public function purchase_history_invoice($id)
    {
        $report = PaymentHistory::findOrFail($id);

        return view('admin.report.report_invoice', compact('report'));
    }

    /**
     * Generate reports for a given revenue type (admin or instructor).
     */
    private function generateReport(Request $request, string $revenueField, string $view): array
    {
        $dateRange = $this->extractDateRange($request->eDateRange);

        $reports = PaymentHistory::where($revenueField, '!=', '')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->latest('id')
            ->paginate(10)
            ->appends($request->query());

        return [
            'start_date' => strtotime($dateRange['start']),
            'end_date' => strtotime($dateRange['end']),
            'reports' => $reports,
        ];
    }

    /**
     * Extract date range from request input or default to current month.
     */
    private function extractDateRange(?string $eDateRange): array
    {
        if ($eDateRange) {
            $dates = explode('-', $eDateRange);
            $start = date('Y-m-d 00:00:00', strtotime(trim($dates[0])));
            $end = date('Y-m-d 23:59:59', strtotime(trim($dates[1])));
        } else {
            $start = date('Y-m-01 00:00:00');
            $end = date('Y-m-t 23:59:59');
        }

        return ['start' => $start, 'end' => $end];
    }
}
