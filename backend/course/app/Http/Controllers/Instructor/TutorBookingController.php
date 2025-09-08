<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\TutorBooking;
use App\Models\TutorCanTeach;
use App\Models\TutorSchedule;
use App\Services\FileUploaderService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class TutorBookingController extends Controller
{
    public function my_subjects()
    {
        $categories = TutorCanTeach::with('category_to_tutorCategory')
            ->where('instructor_id', auth('web')->id())
            ->get()
            ->unique('category_id');

        return view('instructor.tutor_booking.my_subjects', ['categories' => $categories]);
    }

    public function my_subject_add() {}

    public function my_subject_store(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'subject_id' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $data = $request->only(['category_id', 'subject_id', 'description', 'price']);
        $data['instructor_id'] = auth('web')->id();
        $data['created_at'] = now();
        $data['updated_at'] = now();

        if ($request->hasFile('thumbnail')) {
            $path = 'uploads/tutor-booking/subject-thumbnail/'.nice_file_name(random(9), $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $path, 400, null, 200, 200);
            $data['thumbnail'] = $path;
        }

        TutorCanTeach::create($data);

        return redirect()->route('instructor.my_subjects')->with('success', get_phrase('Subject added successfully'));
    }

    public function my_subject_edit() {}

    public function my_subject_update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required',
            'subject_id' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $subject = TutorCanTeach::findOrFail($id);

        $data = $request->only(['category_id', 'subject_id', 'description', 'price']);
        $data['instructor_id'] = auth('web')->id();
        $data['updated_at'] = now();

        if ($request->hasFile('thumbnail')) {
            if ($subject->thumbnail && file_exists(public_path($subject->thumbnail))) {
                unlink(public_path($subject->thumbnail));
            }

            $path = 'uploads/tutor-booking/subject-thumbnail/'.nice_file_name(random(9), $request->thumbnail->extension());
            app(FileUploaderService::class)->upload($request->thumbnail, $path, 400, null, 200, 200);
            $data['thumbnail'] = $path;
        }

        $subject->update($data);

        return redirect()->route('instructor.my_subjects')->with('success', get_phrase('Subject updated successfully'));
    }

    public function my_subject_delete($id)
    {
        TutorCanTeach::where('id', $id)->delete();

        return redirect()->route('instructor.my_subjects')->with('success', get_phrase('Subject deleted successfully'));
    }

    public function my_subject_category_delete($id)
    {
        TutorCanTeach::where('category_id', $id)->delete();

        return redirect()->route('instructor.my_subjects')->with('success', get_phrase('Subjects for the selected category deleted successfully'));
    }

    public function manage_schedules()
    {
        $schedules = TutorSchedule::where('tutor_id', auth('web')->id())->get();

        $schedulesByDate = $schedules->groupBy(function ($item) {
            return date('Y-m-d', $item->start_time);
        });

        $formatted = [];

        foreach ($schedulesByDate as $date => $list) {
            $formatted[] = [
                'title' => count($list).' schedules',
                'start' => $date,
            ];
        }

        return view('instructor.tutor_booking.manage_schedules', [
            'schedules' => json_encode($formatted),
        ]);
    }

    public function manage_schedules_by_date($date)
    {
        $parsedDate = \DateTime::createFromFormat('d-M-y', $date);
        if (! $parsedDate) {
            return back()->with('error', get_phrase('Invalid date format'));
        }

        $dayStart = $parsedDate->setTime(0, 0)->getTimestamp();
        $dayEnd = $parsedDate->setTime(23, 59, 59)->getTimestamp();

        $schedules = TutorSchedule::where('tutor_id', auth('web')->id())
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->paginate(10);

        return view('instructor.tutor_booking.schedules_by_day', compact('schedules', 'date'));
    }

    public function schedule_edit($id = '')
    {
        $categories = TutorCanTeach::with('category_to_tutorCategory')
            ->where('instructor_id', auth('web')->id())
            ->get()
            ->unique('category_id');

        return view('instructor.tutor_booking.edit_schedule', [
            'schedule_details' => TutorSchedule::find($id),
            'categories' => $categories,
        ]);
    }

    public function schedule_update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'start_time' => 'required|date',
            'duration' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_time']);

        TutorSchedule::findOrFail($id)->update([
            'category_id' => $validated['category_id'],
            'subject_id' => $validated['subject_id'],
            'start_time' => $startDate->timestamp,
            'end_time' => $startDate->copy()->addMinutes($validated['duration'])->timestamp,
            'duration' => $validated['duration'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('instructor.manage_schedules_by_date', ['date' => $startDate->format('d-M-y')])
            ->with('success', get_phrase('Schedule updated successfully.'));
    }

    public function schedule_delete($id)
    {
        TutorSchedule::findOrFail($id)->delete();

        return back()->with('success', get_phrase('Schedule deleted successfully.'));
    }

    public function add_schedule()
    {
        $categories = TutorCanTeach::with('category_to_tutorCategory')
            ->where('instructor_id', auth('web')->id())
            ->get()
            ->unique('category_id');

        return view('instructor.tutor_booking.add_schedule', ['categories' => $categories]);
    }

    public function schedule_store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'tution_type' => 'required|in:0,1',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'duration' => 'required|integer',
            'description' => 'nullable|string',
            '1_day' => 'nullable|array',
        ]);

        $duration = $validated['duration'];
        $startDate = Carbon::parse($validated['start_time']);

        if ($validated['tution_type'] == 0 && ! empty($validated['end_time'])) {
            $endDate = Carbon::parse($validated['end_time']);
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                if (in_array(strtolower($date->format('l')), $validated['1_day'] ?? [])) {
                    TutorSchedule::create([
                        'tutor_id' => auth('web')->id(),
                        'category_id' => $validated['category_id'],
                        'subject_id' => $validated['subject_id'],
                        'start_time' => $date->timestamp,
                        'end_time' => $date->copy()->addMinutes($duration)->timestamp,
                        'duration' => $duration,
                        'description' => $validated['description'],
                        'tution_type' => 0,
                    ]);
                }
            }
        } else {
            TutorSchedule::create([
                'tutor_id' => auth('web')->id(),
                'category_id' => $validated['category_id'],
                'subject_id' => $validated['subject_id'],
                'start_time' => $startDate->timestamp,
                'end_time' => $startDate->copy()->addMinutes($duration)->timestamp,
                'duration' => $duration,
                'description' => $validated['description'],
                'tution_type' => 1,
            ]);
        }

        return redirect()->route('instructor.manage_schedules')->with('success', get_phrase('Schedule successfully created.'));
    }

    public function subject_by_category_id(Request $request)
    {
        if ($request->filled('category_id')) {
            $teaches = TutorCanTeach::where('category_id', $request->category_id)->get();

            return view('instructor.tutor_booking.load_subjects', compact('teaches'));
        }
    }

    public function tutor_booking_list()
    {
        $today = strtotime('today');

        $baseQuery = function ($isArchive) use ($today) {
            $query = TutorBooking::join('tutor_schedules', 'tutor_bookings.schedule_id', '=', 'tutor_schedules.id')
                ->join('tutor_subjects', 'tutor_schedules.subject_id', '=', 'tutor_subjects.id')
                ->join('users', 'tutor_bookings.student_id', '=', 'users.id')
                ->select('tutor_bookings.*', 'tutor_subjects.name', 'users.name as student_name')
                ->orderByDesc('tutor_bookings.id');

            $query->where('tutor_bookings.start_time', $isArchive ? '<' : '>=', $today);

            if (request()->filled('search')) {
                $search = request()->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                        ->orWhere('tutor_subjects.name', 'like', "%{$search}%");
                });
            }

            return $query;
        };

        $page_data['booking_list'] = $baseQuery(false)->paginate(20)->appends(request()->query());
        $page_data['archive_list'] = $baseQuery(true)->paginate(20, ['*'], 'archive_page')->appends(request()->query());

        return view('instructor.tutor_booking.tutor_booking_list', $page_data);
    }

    public function join_class($booking_id = '')
    {
        $booking = TutorBooking::findOrFail($booking_id);

        if (empty($booking->joining_data)) {
            $meetingInfo = json_decode($this->create_zoom_meeting(
                $booking->booking_to_schedule->schedule_to_tutorSubjects->name,
                $booking->start_time
            ), true);

            if (isset($meetingInfo['code'])) {
                return back()->with('error', get_phrase($meetingInfo['message']));
            }

            $booking->update(['joining_data' => json_encode($meetingInfo)]);
        } else {
            $meetingInfo = json_decode($booking->joining_data, true);
        }

        $now = time();
        $allowedJoinTime = $now + (15 * 60); // 15 mins early join

        $validBooking = TutorBooking::where('id', $booking_id)
            ->where('start_time', '<', $allowedJoinTime)
            ->where('end_time', '>', $now)
            ->where('tutor_id', auth('web')->id())
            ->first();

        if (! $validBooking) {
            return back()->with('error', get_phrase('Session not found.'));
        }

        if (get_settings('zoom_web_sdk') === 'active') {
            return view('instructor.tutor_booking.join_tution', [
                'booking' => $validBooking,
                'user' => get_user_info($validBooking->tutor_id),
                'is_host' => 1,
            ]);
        }

        return redirect($meetingInfo['start_url']);
    }

    public function create_zoom_meeting($topic, $dateAndTime)
    {
        $zoomEmail = get_settings('zoom_account_email');
        $token = $this->create_zoom_token();

        $meetingData = [
            'topic' => $topic,
            'schedule_for' => $zoomEmail,
            'type' => 2,
            'start_time' => date('Y-m-d\TH:i:s', strtotime($dateAndTime)),
            'duration' => 60,
            'timezone' => get_settings('timezone'),
            'settings' => [
                'approval_type' => 2,
                'join_before_host' => true,
                'jbh_time' => 0,
            ],
        ];

        $headers = [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ];

        $ch = curl_init('https://api.zoom.us/v2/users/me/meetings');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($meetingData),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function create_zoom_token()
    {
        $clientId = get_settings('zoom_client_id');
        $clientSecret = get_settings('zoom_client_secret');
        $accountId = get_settings('zoom_account_id');

        $authHeader = 'Basic '.base64_encode($clientId.':'.$clientSecret);
        $tokenUrl = 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id='.$accountId;

        $curl = curl_init($tokenUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: '.$authHeader,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        return $data['access_token'] ?? '';
    }
}
