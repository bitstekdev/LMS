<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Payout;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UsersController extends Controller
{
    // ========== ADMIN SECTION ==========

    public function admin_index(Request $request)
    {
        $query = User::where('role', 'admin');
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('email', 'LIKE', '%'.$request->search.'%');
            });
        }
        $page_data['admins'] = $query->paginate(10);

        return view('admin.admin.index', $page_data);
    }

    public function admin_create()
    {
        return view('admin.admin.create_admin');
    }

    public function admin_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $data = $request->only(['name', 'about', 'phone', 'address', 'email', 'facebook', 'twitter', 'website', 'linkedin']);
        $data['role'] = 'admin';
        $data['status'] = 1;
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $path = 'uploads/users/instructor/'.nice_file_name($request->name, $request->photo->extension());
            app(FileUploaderService::class)->upload($request->photo, $path, 400, null, 200, 200);
            $data['photo'] = $path;
        }

        $user = User::create($data);
        Permission::create(['admin_id' => $user->id]);

        Session::flash('success', get_phrase('Admin added successfully'));

        return redirect()->route('admin.admins.index');
    }

    public function admin_edit($id)
    {
        $page_data['admin'] = User::findOrFail($id);

        return view('admin.admin.edit_admin', $page_data);
    }

    public function admin_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => "required|email|unique:users,email,$id",
        ]);

        $data = $request->only(['name', 'about', 'phone', 'address', 'email', 'facebook', 'twitter', 'website', 'linkedin']);

        if ($request->hasFile('photo')) {
            $user = User::findOrFail($id);
            remove_file($user->photo);
            $path = 'uploads/users/instructor/'.nice_file_name($request->name, $request->photo->extension());
            app(FileUploaderService::class)->upload($request->photo, $path, 400, null, 200, 200);
            $data['photo'] = $path;
        }

        User::where('id', $id)->update($data);
        Session::flash('success', get_phrase('Admin updated successfully'));

        return redirect()->route('admin.admins.index');
    }

    public function admin_delete($id)
    {
        $threads = MessageThread::where('contact_one', $id)->orWhere('contact_two', $id)->pluck('id');

        if ($threads->isNotEmpty()) {
            Message::whereIn('thread_id', $threads)->delete();
            MessageThread::whereIn('id', $threads)->delete();
        }

        User::destroy($id);
        Permission::where('admin_id', $id)->delete();

        Session::flash('success', get_phrase('Admin deleted successfully'));

        return redirect()->back();
    }

    public function admin_permission($user_id)
    {
        $page_data['admin'] = User::findOrFail($user_id);

        return view('admin.admin.permission', $page_data);
    }

    public function admin_permission_store(Request $request)
    {
        $permission = Permission::firstOrNew(['admin_id' => $request->user_id]);
        $existing = json_decode($permission->permissions ?? '[]', true);

        if (in_array($request->permission, $existing)) {
            $existing = array_diff($existing, [$request->permission]);
        } else {
            $existing[] = $request->permission;
        }

        $permission->permissions = json_encode(array_values($existing));
        $permission->save();

        return true;
    }
    // ========== INSTRUCTOR SECTION ==========

    public function instructor_index()
    {
        $query = User::where('role', 'instructor');
        if (request()->get('search')) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%'.request()->get('search').'%')
                    ->orWhere('email', 'LIKE', '%'.request()->get('search').'%');
            });
        }
        $page_data['instructors'] = $query->paginate(10);

        return view('admin.instructor.index', $page_data);
    }

    public function instructor_create()
    {
        return view('admin.instructor.create_instructor');
    }

    public function instructor_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $data = $request->only(['name', 'about', 'phone', 'address', 'email', 'facebook', 'twitter', 'website', 'linkedin']);
        $data['paymentkeys'] = json_encode($request->paymentkeys);
        $data['role'] = 'instructor';
        $data['status'] = 1;
        $data['password'] = Hash::make($request->password);
        $data['email_verified_at'] = $request->email_verified == 1 ? now() : (get_settings('student_email_verification') != 1 ? now() : null);

        if ($request->hasFile('photo')) {
            $path = 'uploads/users/instructor/'.nice_file_name($request->name, $request->photo->extension());
            app(FileUploaderService::class)->upload($request->photo, $path, 400, null, 200, 200);
            $data['photo'] = $path;
        }

        $user = User::create($data);

        if (get_settings('student_email_verification') == 1) {
            $user->sendEmailVerificationNotification();
        }

        Session::flash('success', get_phrase('Instructor added successfully'));

        return redirect()->route('admin.instructor.index');
    }

    public function instructor_edit($id)
    {
        $page_data['instructor'] = User::findOrFail($id);

        return view('admin.instructor.edit_instructor', $page_data);
    }

    public function instructor_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => "required|email|unique:users,email,$id",
        ]);

        $data = $request->only(['name', 'about', 'phone', 'address', 'email', 'facebook', 'twitter', 'website', 'linkedin']);
        $data['paymentkeys'] = json_encode($request->paymentkeys);

        if ($request->hasFile('photo')) {
            $user = User::findOrFail($id);
            remove_file($user->photo);
            $path = 'uploads/users/instructor/'.nice_file_name($request->name, $request->photo->extension());
            app(FileUploaderService::class)->upload($request->photo, $path, 400, null, 200, 200);
            $data['photo'] = $path;
        }

        User::where('id', $id)->update($data);

        Session::flash('success', get_phrase('Instructor updated successfully'));

        return redirect()->route('admin.instructor.index');
    }

    public function instructor_delete($id)
    {
        $threads = MessageThread::where('contact_one', $id)->orWhere('contact_two', $id)->pluck('id');

        if ($threads->isNotEmpty()) {
            Message::whereIn('thread_id', $threads)->delete();
            MessageThread::whereIn('id', $threads)->delete();
        }

        User::destroy($id);
        Session::flash('success', get_phrase('Instructor deleted successfully'));

        return redirect()->back();
    }

    public function instructor_view_course(Request $request)
    {
        return Course::where('user_id', $request->id)->get();
    }

    public function instructor_payout(Request $request)
    {
        $start_date = strtotime('first day of this month');
        $end_date = strtotime('last day of this month');

        $page_data['start_date'] = $start_date;
        $page_data['end_date'] = $end_date;

        $start = date('Y-m-d 00:00:00', $start_date);
        $end = date('Y-m-d 23:59:59', $end_date);

        $page_data['instructor_payout_complete'] = Payout::where('status', 1)
            ->whereBetween('created_at', [$start, $end])
            ->paginate(10);

        $page_data['instructor_payout_incomplete'] = Payout::where('status', 0)
            ->whereBetween('created_at', [$start, $end])
            ->paginate(10);

        return view('admin.instructor.payout', $page_data);
    }

    public function instructor_payout_filter(Request $request)
    {
        $date = explode('-', $request->eDateRange);

        $start_date = strtotime(trim($date[0]).' 00:00:00');
        $end_date = strtotime(trim($date[1]).' 23:59:59');

        $start = date('Y-m-d 00:00:00', $start_date);
        $end = date('Y-m-d 23:59:59', $end_date);

        $page_data['start_date'] = $start_date;
        $page_data['end_date'] = $end_date;

        $page_data['instructor_payout_complete'] = Payout::where('status', 1)
            ->whereBetween('created_at', [$start, $end])
            ->paginate(10);

        $page_data['instructor_payout_incomplete'] = Payout::where('status', 0)
            ->whereBetween('created_at', [$start, $end])
            ->paginate(10);

        return view('admin.instructor.payout', $page_data);
    }

    public function instructor_payout_invoice($id = '')
    {
        if (! empty($id)) {
            $page_data['invoice_info'] = Payout::where('id', $id)->first();
            $page_data['invoice_data'] = Payout::where('status', 1)->get();
            $page_data['invoice_id'] = $id;

            return view('admin.instructor.instructor_invoice', $page_data);
        }

        return redirect()->route('admin.instructor.payout')->with('error', 'Invalid invoice ID.');
    }

    public function instructor_payment(Request $request)
    {
        $id = $request->user_id;
        $payable_amount = $request->amount;

        $payment_details = [
            'items' => [
                [
                    'id' => $id,
                    'title' => get_phrase('Pay for instructor payout'),
                    'subtitle' => '',
                    'price' => $payable_amount,
                    'discount_price' => $payable_amount,
                    'discount_percentage' => 0,
                ],
            ],
            'custom_field' => [
                'start_date' => now()->format('Y-m-d H:i:s'),
                'end_date' => now()->format('Y-m-d H:i:s'),
                'user_id' => auth('web')->id(),
                'payout_id' => $request->payout_id,
            ],
            'success_method' => [
                'model_name' => 'InstructorPayment',
                'function_name' => 'instructor_payment',
            ],
            'tax' => 0,
            'coupon' => null,
            'payable_amount' => $payable_amount,
            'cancel_url' => route('admin.instructor.payout'),
            'success_url' => route('payment.success'),
        ];

        session(['payment_details' => $payment_details]);

        return redirect()->route('payment');
    }

    public function instructor_setting()
    {
        $page_data['allow_instructor'] = Setting::where('type', 'allow_instructor')->first();
        $page_data['application_note'] = Setting::where('type', 'instructor_application_note')->first();
        $page_data['instructor_revenue'] = Setting::where('type', 'instructor_revenue')->first();

        return view('admin.instructor.instructor_setting', $page_data);
    }

    public function instructor_setting_store(Request $request)
    {
        if ($request->first === 'item_1') {
            Setting::updateOrCreate(
                ['type' => 'instructor_application_note'],
                ['description' => $request->instructor_application_note]
            );

            Setting::updateOrCreate(
                ['type' => 'allow_instructor'],
                ['description' => $request->allow_instructor]
            );
        }

        if ($request->second === 'item_2') {
            Setting::updateOrCreate(
                ['type' => 'instructor_revenue'],
                ['description' => $request->instructor_revenue]
            );
        }

        Session::flash('success', get_phrase('Instructor setting updated'));

        return redirect()->back();
    }

    public function instructor_application()
    {
        return view('admin.instructor.application');
    }

    public function instructor_application_approve($id)
    {
        $application = Application::findOrFail($id);
        $application->update(['status' => 1]);

        User::where('id', $application->user_id)->update(['role' => 'instructor']);

        Session::flash('success', get_phrase('Application approved successfully'));

        return redirect()->back();
    }

    public function instructor_application_delete($id)
    {
        Application::destroy($id);
        Session::flash('success', get_phrase('Application deleted successfully'));

        return redirect()->back();
    }

    public function instructor_application_download($id)
    {
        $application = Application::findOrFail($id);

        if (file_exists(public_path($application->document))) {
            return response()->download(public_path($application->document));
        }

        Session::flash('error', get_phrase('File does not exist'));

        return redirect()->back();
    }

    public function revokeAccess($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'instructor') {
            $user->update(['role' => 'student']);
            Session::flash('success', get_phrase('Instructor access revoked successfully.'));
        } else {
            Session::flash('error', get_phrase('This user is not an instructor.'));
        }

        return redirect()->back();
    }
    // ========== STUDENT SECTION ==========

    public function student_index()
    {
        $query = User::where('role', 'student');
        if (request()->get('search')) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%'.request()->get('search').'%')
                    ->orWhere('email', 'LIKE', '%'.request()->get('search').'%');
            });
        }
        $page_data['students'] = $query->paginate(10);

        return view('admin.student.index', $page_data);
    }

    public function student_create()
    {
        return view('admin.student.create_student');
    }

    public function student_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $data = $request->only(['name', 'about', 'phone', 'address', 'email', 'facebook', 'twitter', 'website', 'linkedin']);
        $data['paymentkeys'] = json_encode($request->paymentkeys);
        $data['role'] = 'student';
        $data['status'] = 1;
        $data['password'] = Hash::make($request->password);
        $data['email_verified_at'] = $request->email_verified == 1 ? now() : (get_settings('student_email_verification') != 1 ? now() : null);

        if ($request->hasFile('photo')) {
            $path = 'uploads/users/student/'.nice_file_name($request->name, $request->photo->extension());
            app(FileUploaderService::class)->upload($request->photo, $path, 400, null, 200, 200);
            $data['photo'] = $path;
        }

        $user = User::create($data);

        if (get_settings('student_email_verification') == 1) {
            $user->sendEmailVerificationNotification();
        }

        Session::flash('success', get_phrase('Student added successfully'));

        return redirect()->route('admin.student.index');
    }

    public function student_edit($id)
    {
        $page_data['student'] = User::findOrFail($id);

        return view('admin.student.edit_student', $page_data);
    }

    public function student_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => "required|email|unique:users,email,$id",
        ]);

        $data = $request->only(['name', 'about', 'phone', 'address', 'email', 'facebook', 'twitter', 'website', 'linkedin']);
        $data['paymentkeys'] = json_encode($request->paymentkeys);

        if ($request->hasFile('photo')) {
            $user = User::findOrFail($id);
            remove_file($user->photo);
            $path = 'uploads/users/student/'.nice_file_name($request->name, $request->photo->extension());
            app(FileUploaderService::class)->upload($request->photo, $path, 400, null, 200, 200);
            $data['photo'] = $path;
        }

        User::where('id', $id)->update($data);
        Session::flash('success', get_phrase('Student updated successfully'));

        return redirect()->route('admin.student.index');
    }

    public function student_delete($id)
    {
        $threads = MessageThread::where('contact_one', $id)->orWhere('contact_two', $id)->pluck('id');

        if ($threads->isNotEmpty()) {
            Message::whereIn('thread_id', $threads)->delete();
            MessageThread::whereIn('id', $threads)->delete();
        }

        $user = User::findOrFail($id);
        remove_file($user->photo);
        $user->delete();

        Session::flash('success', get_phrase('Student deleted successfully'));

        return redirect()->route('admin.student.index');
    }

    // ========== ENROLLMENTS ==========

    public function student_enrol()
    {
        return view('admin.enroll.course_enrollment');
    }

    public function student_get(Request $request)
    {
        $users = User::where('role', 'student')
            ->where('name', 'like', '%'.$request->searchVal.'%')->get();

        $response = [];
        foreach ($users as $user) {
            $response[] = ['id' => $user->id, 'text' => $user->name];
        }

        return response()->json($response);
    }

    public function student_post(Request $request)
    {
        foreach ($request->user_id as $user_id) {
            foreach ($request->course_id as $course_id) {
                $course_details = get_course_info($course_id);

                $enroll_data = [
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'entry_date' => time(),
                    'expiry_date' => $course_details->expiry_period > 0 ? strtotime('+'.($course_details->expiry_period * 30).' days') : null,
                ];

                $already_enrolled = Enrollment::where('user_id', $user_id)->where('course_id', $course_id)->exists();
                if (! $already_enrolled) {
                    Enrollment::create($enroll_data);
                }
            }
        }

        Session::flash('success', get_phrase('Student enrolled successfully'));

        return redirect()->route('admin.enroll.history');
    }

    public function enroll_history(Request $request)
    {
        if ($request->eDateRange) {
            [$start, $end] = explode('-', $request->eDateRange);
            $start_date = strtotime(trim($start).' 00:00:00');
            $end_date = strtotime(trim($end).' 23:59:59');
        } else {
            $start_date = strtotime('first day of this month');
            $end_date = strtotime('last day of this month');
        }

        $page_data['start_date'] = $start_date;
        $page_data['end_date'] = $end_date;

        $page_data['enroll_history'] = Enrollment::whereBetween('entry_date', [$start_date, $end_date])
            ->paginate(10);

        return view('admin.enroll.enroll_history', $page_data);
    }

    public function enroll_history_delete($id)
    {
        Enrollment::destroy($id);
        Session::flash('success', get_phrase('Enrollment deleted successfully'));

        return redirect()->back();
    }

    // ========== PROFILE MANAGEMENT ==========

    public function manage_profile()
    {
        return view('admin.profile.index');
    }

    public function manage_profile_update(Request $request)
    {
        if ($request->type == 'general') {
            $profile = $request->only(['name', 'email', 'facebook', 'linkedin', 'twitter', 'about', 'skills', 'biography']);

            if ($request->hasFile('photo')) {
                $profile['photo'] = 'uploads/users/admin/'.nice_file_name($request->name, $request->photo->extension());
                app(FileUploaderService::class)->upload($request->photo, $profile['photo'], 400, null, 200, 200);
            }

            User::where('id', auth('web')->id())->update($profile);

        } elseif ($request->type == 'password') {

            if (! Auth::attempt(['email' => auth('web')->user()->email, 'password' => $request->current_password])) {
                Session::flash('error', get_phrase('Current password is incorrect.'));

                return redirect()->back();
            }

            if ($request->new_password !== $request->confirm_password) {
                Session::flash('error', get_phrase('Passwords do not match.'));

                return redirect()->back();
            }

            User::where('id', auth('web')->id())->update(['password' => Hash::make($request->new_password)]);
        }

        Session::flash('success', get_phrase('Profile updated successfully.'));

        return redirect()->back();
    }
}
