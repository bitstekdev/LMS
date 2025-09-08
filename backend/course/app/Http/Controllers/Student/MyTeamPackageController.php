<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\OfflinePayment;
use App\Models\TeamPackageMember;
use App\Models\TeamPackagePurchase;
use App\Models\TeamTrainingPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MyTeamPackageController extends Controller
{
    public function index()
    {
        // Fetch active team packages
        $page_data['packages'] = TeamPackagePurchase::join('team_training_packages', 'team_package_purchases.package_id', '=', 'team_training_packages.id')
            ->join('courses', 'team_training_packages.course_id', '=', 'courses.id')
            ->where('team_package_purchases.user_id', auth('web')->id())
            ->where('team_package_purchases.status', 1)
            ->select('team_training_packages.*', 'courses.title as course_title', 'courses.slug as course_slug')
            ->latest('team_package_purchases.id')
            ->paginate(10)
            ->appends(request()->query());

        // Deactivate expired packages
        $expired_packages = TeamPackagePurchase::join('team_training_packages', 'team_package_purchases.package_id', '=', 'team_training_packages.id')
            ->where('team_package_purchases.status', 1)
            ->where('team_package_purchases.user_id', auth('web')->id())
            ->where('team_training_packages.expiry_type', 'limited')
            ->where('team_training_packages.expiry_date', '<', time())
            ->pluck('team_package_purchases.package_id');

        if ($expired_packages->isNotEmpty()) {
            TeamPackagePurchase::where('user_id', auth('web')->id())
                ->whereIn('package_id', $expired_packages)
                ->update(['status' => 0]);
        }

        return view('frontend.default.student.my_team_packages.index', $page_data);
    }

    public function show($slug)
    {
        // Validate package ownership
        $package = TeamPackagePurchase::join('team_training_packages', 'team_package_purchases.package_id', '=', 'team_training_packages.id')
            ->join('courses', 'team_training_packages.course_id', '=', 'courses.id')
            ->where('team_package_purchases.user_id', auth('web')->id())
            ->where('team_package_purchases.status', 1)
            ->where('team_training_packages.slug', $slug)
            ->select('team_training_packages.*', 'courses.title as course_title', 'courses.slug as course_slug')
            ->first();

        if (! $package) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        // Team members
        $page_data['package'] = $package;
        $page_data['members'] = TeamPackageMember::join('users', 'team_package_members.member_id', '=', 'users.id')
            ->select('team_package_members.*', 'users.name', 'users.email', 'users.photo')
            ->where([
                'leader_id' => auth('web')->id(),
                'team_package_id' => $package->id,
            ])
            ->paginate(10);

        // Purchase history
        $page_data['purchased_packages'] = TeamPackagePurchase::where([
            'user_id' => auth('web')->id(),
            'package_id' => $package->id,
        ])
            ->join('team_training_packages', 'team_package_purchases.package_id', '=', 'team_training_packages.id')
            ->select('team_package_purchases.*', 'team_training_packages.title', 'team_training_packages.slug')
            ->latest('team_package_purchases.id')
            ->paginate(15);

        return view('frontend.default.student.my_team_packages.details', $page_data);
    }

    public function search_members(Request $request, $package_id = '')
    {
        $user = User::where('email', $request->email)->first();

        if ($package_id && $user) {
            $status = TeamPackageMember::where('team_package_id', $package_id)
                ->where('member_id', $user->id)
                ->exists();

            $page_data = [
                'user' => $user,
                'status' => $status,
                'package_id' => $package_id,
            ];

            return view('frontend.default.student.my_team_packages.search_member', $page_data);
        }

        Session::flash('error', get_phrase('User not found.'));

        return redirect()->back();
    }

    public function member_action($action)
    {
        $packageId = request('package_id');
        $userId = request('user_id');

        $package = TeamTrainingPackage::find($packageId);
        $user = User::find($userId);

        if (! $package || ! $user) {
            Session::flash('error', get_phrase('Invalid request.'));

            return redirect()->back();
        }

        if ($user->id === auth('web')->id()) {
            Session::flash('error', get_phrase('You are the team leader.'));

            return redirect()->back();
        }

        $isPurchased = TeamPackagePurchase::where('user_id', auth('web')->id())
            ->where('package_id', $package->id)
            ->exists();

        if (! $isPurchased) {
            Session::flash('error', get_phrase('Forbidden! Access denied.'));

            return redirect()->back();
        }

        $member = TeamPackageMember::where('leader_id', auth('web')->id())
            ->where('team_package_id', $package->id)
            ->where('member_id', $user->id)
            ->first();

        $existing_enrollment = Enrollment::where('course_id', $package->course_id)
            ->where('user_id', $user->id)
            ->first();

        if ($action === 'register') {
            if (reserved_team_members($package->id) >= $package->allocation) {
                return redirect()->back()->with('error', get_phrase('Not enough space to add a member.'));
            }

            if (! $member) {
                TeamPackageMember::create([
                    'leader_id' => auth('web')->id(),
                    'team_package_id' => $package->id,
                    'member_id' => $user->id,
                ]);

                if (! $existing_enrollment) {
                    Enrollment::create([
                        'course_id' => $package->course_id,
                        'user_id' => $user->id,
                        'enrollment_type' => 'team_package',
                        'entry_date' => now()->timestamp,
                        'expiry_date' => $package->expiry_type === 'limited' ? $package->expiry_date : null,
                    ]);
                } elseif ($package->expiry_type === 'limited') {
                    $expiry = max($existing_enrollment->expiry_date, $package->expiry_date);
                    $existing_enrollment->update(['expiry_date' => $expiry]);
                }

                Session::flash('success', get_phrase('Member has been added to the team.'));
            } else {
                Session::flash('error', get_phrase('Member already exists in the team.'));
            }
        } elseif ($action === 'remove') {
            if ($member) {
                $member->delete();
                Enrollment::where('course_id', $package->course_id)->where('user_id', $user->id)->delete();
                Session::flash('success', get_phrase('Member has been removed from the team.'));
            } else {
                Session::flash('error', get_phrase('Member not found in the team.'));
            }
        }

        return redirect()->back();
    }

    public function purchase($id)
    {
        $package = TeamTrainingPackage::find($id);

        if (! $package) {
            Session::flash('error', get_phrase('Package not found.'));

            return redirect()->back();
        }

        if ($package->user_id === auth('web')->id()) {
            Session::flash('error', get_phrase('You own this item.'));

            return redirect()->back();
        }

        if (TeamPackagePurchase::where('user_id', auth('web')->id())->where('package_id', $id)->where('status', 1)->exists()) {
            Session::flash('error', get_phrase('Item is already purchased.'));

            return redirect()->back();
        }

        $pending = OfflinePayment::where([
            'user_id' => auth('web')->id(),
            'items' => $package->id,
            'item_type' => 'team_package',
            'status' => 0,
        ])->exists();

        if ($pending) {
            Session::flash('warning', get_phrase('Your request is in process.'));

            return redirect()->back();
        }

        $payment_details = [
            'items' => [
                [
                    'id' => $package->id,
                    'title' => $package->title,
                    'subtitle' => '',
                    'price' => $package->price,
                    'discount_price' => '',
                ],
            ],
            'custom_field' => [
                'item_type' => 'package',
                'pay_for' => get_phrase('Team Training Package'),
            ],
            'success_method' => [
                'model_name' => 'TeamPackagePurchase',
                'function_name' => 'purchase_team_package',
            ],
            'payable_amount' => round($package->price, 2),
            'tax' => 0,
            'cancel_url' => route('team.package.details', $package->slug),
            'success_url' => route('payment.success', ''),
        ];

        Session::put(['payment_details' => $payment_details]);

        return redirect()->route('payment');
    }

    public function invoice($id)
    {
        $package = TeamPackagePurchase::join('team_training_packages', 'team_package_purchases.package_id', '=', 'team_training_packages.id')
            ->join('users', 'team_package_purchases.user_id', '=', 'users.id')
            ->select('team_package_purchases.*', 'team_training_packages.title', 'team_training_packages.slug', 'users.name as user_name')
            ->where('team_package_purchases.user_id', auth('web')->id())
            ->where('team_package_purchases.id', $id)
            ->first();

        if (! $package) {
            Session::flash('error', get_phrase('Invoice not found.'));

            return redirect()->back();
        }

        return view('frontend.default.student.my_team_packages.print_invoice', ['package' => $package]);
    }
}
