<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TutorCanTeach;
use App\Models\TutorCategory;
use App\Models\TutorReview;
use App\Models\TutorSchedule;
use App\Models\TutorSubject;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;

class TutorBookingController extends Controller
{
    public function index(Request $request)
    {
        $tutorIds = TutorSchedule::distinct()->pluck('tutor_id')->toArray();
        $query = User::whereIn('id', $tutorIds);

        // Search by tutor name
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%'.$request->search.'%');
        }

        // Filter by category and subject
        $filteredTutorIds = $tutorIds;
        if ($request->filled('category')) {
            $categoryId = TutorCategory::where('slug', $request->category)->value('id');
            $categoryTutorIds = TutorSchedule::where('category_id', $categoryId)->pluck('tutor_id')->toArray();
            $filteredTutorIds = array_intersect($filteredTutorIds, $categoryTutorIds);
        }

        if ($request->filled('subject')) {
            $subjectId = TutorSubject::where('slug', $request->subject)->value('id');
            $subjectTutorIds = TutorSchedule::where('subject_id', $subjectId)->pluck('tutor_id')->toArray();
            $filteredTutorIds = array_intersect($filteredTutorIds, $subjectTutorIds);
        }

        // Filter by price range
        if ($request->filled('min_fee') && $request->filled('max_fee')) {
            $priceTutorIds = TutorCanTeach::whereBetween('price', [$request->min_fee, $request->max_fee])->pluck('instructor_id')->toArray();
            $filteredTutorIds = array_intersect($filteredTutorIds, $priceTutorIds);
        }

        // Filter by average rating
        if ($request->filled('rating')) {
            $ratingTutorIds = TutorReview::select('tutor_id')
                ->groupBy('tutor_id')
                ->havingRaw('AVG(rating) = ?', [$request->rating])
                ->pluck('tutor_id')
                ->toArray();
            $filteredTutorIds = array_intersect($filteredTutorIds, $ratingTutorIds);
        }

        $page_data['tutors'] = $query->whereIn('id', $filteredTutorIds)->paginate(10)->appends($request->query());
        $page_data['categories'] = TutorCategory::where('status', 1)->get();
        $page_data['subjects'] = TutorSubject::where('status', 1)->get();

        return view(theme_path().'tutor_booking.index', $page_data);
    }

    public function tutor_schedule(Request $request, $id, $user)
    {
        /** @var array $page_data */
        $page_data = [];

        $page_data['tutor_details'] = User::findOrFail($id);
        $todayStart = strtotime('today');
        $todayEnd = strtotime('tomorrow') - 1;

        $page_data['schedules'] = TutorSchedule::where('tutor_id', $id)
            ->whereBetween('start_time', [$todayStart, $todayEnd])
            ->get();

        // Swiper date data
        $page_data['dateSwiperData'] = [];
        $today = new DateTime;
        $twoYearsFromNow = (clone $today)->modify('+2 years');

        while ($today <= $twoYearsFromNow) {
            $page_data['dateSwiperData'][] = [
                'day' => $today->format('d'),
                'month' => $today->format('M'),
                'year' => $today->format('Y'),
                'dayName' => $today->format('D'),
                'isToday' => $today->format('Y-m-d') === now()->format('Y-m-d'),
            ];
            $today->modify('+1 day');
        }

        $page_data['reviews'] = TutorReview::where('tutor_id', $id)->get();

        return view(theme_path().'tutor_booking.tutor_schedule', $page_data);
    }

    public function getSchedulesForDate($date, $tutor_id)
    {
        $startOfDay = strtotime($date);
        $endOfDay = strtotime('+1 day', $startOfDay) - 1;

        $schedules = TutorSchedule::where('tutor_id', $tutor_id)
            ->whereBetween('start_time', [$startOfDay, $endOfDay])
            ->get();

        return view('frontend.default.tutor_booking.schedules', compact('schedules'));
    }

    public function getSchedulesByCalenderDate($date, $tutor_id)
    {
        $startOfDay = strtotime($date);
        $endOfDay = strtotime('+1 day', $startOfDay) - 1;

        $page_data['schedules'] = TutorSchedule::where('tutor_id', $tutor_id)
            ->whereBetween('start_time', [$startOfDay, $endOfDay])
            ->get();

        // Swiper calendar generation
        $page_data['dateSwiperData'] = [];
        $selectedDay = new DateTime($date);
        $twoYearsFromNow = (clone $selectedDay)->modify('+2 years');

        while ($selectedDay <= $twoYearsFromNow) {
            $page_data['dateSwiperData'][] = [
                'day' => $selectedDay->format('d'),
                'month' => $selectedDay->format('M'),
                'year' => $selectedDay->format('Y'),
                'dayName' => $selectedDay->format('D'),
                'isToday' => $selectedDay->format('Y-m-d') === $date,
            ];
            $selectedDay->modify('+1 day');
        }

        return view('frontend.default.tutor_booking.schedules_tab', $page_data);
    }
}
