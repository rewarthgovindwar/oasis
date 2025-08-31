<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\InfixModuleManager;
use App\SmAcademicYear;
use App\SmDateFormat;
use App\SmLanguage;
use App\SmParent;
use App\SmStaff;
use App\SmsTemplate;
use App\SmUserLog;
use App\User;
use Artisan;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Modules\University\Entities\UnAcademicYear;
use Request;

class StaffAsParentController extends Controller
{
    public function loginAsRole()
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $staff = $user->staff;
        $previous_role_id = $staff->previous_role_id;
        $staff->update([
            'role_id' => $previous_role_id,
        ]);
        $user->role_id = $previous_role_id;
        $user->save();

        $this->loginSession($user->id);

        return redirect()->route('admin-dashboard');
    }

    public function loginAsParent()
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $staff = $user->staff;
        $staff->update([
            'previous_role_id' => $user->role_id,
            'role_id' => 3,
        ]);
        $user->role_id = 3;
        $user->save();
        $this->loginSession($user->id);

        return redirect()->route('parent-dashboard');

    }

    public function staff($email = null, $mobile = null, $staff_id = null)
    {
        if ($email && $mobile && $staff_id) {
            return null;
        }

        if ($staff_id) {
            return $staff = SmStaff::find($staff_id);
        }

        $staff = SmStaff::when($mobile && ! $email, function ($q) use ($mobile): void {
            $q->where('mobile', $mobile);
        })
            ->when($email && ! $mobile, function ($q) use ($email): void {
                $q->where('email', $email);
            })
            ->when($email && $mobile, function ($q) use ($mobile): void {
                $q->where('mobile', $mobile);
            })
            ->first(['id', 'parent_id', 'user_id']);
        if (! $staff && ($email && $mobile)) {
            return SmStaff::where('email', $email)->first(['id', 'parent_id', 'user_id']);
        }

        return $staff;
    }

    public function parent($email = null, $mobile = null)
    {
        if (! $email && ! $mobile) {
            return null;
        }

        return SmParent::when($mobile && ! $email, function ($q) use ($mobile): void {
            $q->where('guardians_mobile', $mobile);
        })
            ->when($email && ! $mobile, function ($q) use ($email): void {
                $q->where('guardians_email', $email);
            })
            ->when($email && $mobile, function ($q) use ($mobile): void {
                $q->where('guardians_mobile', $mobile);
            })
            ->first();
    }

    public function staffParentStore($staff, $request, $academic_year)
    {
        $guardians_relation = $request->guardians_relation;
        $relation = $request->relation;

        if (! $staff && $request->staff_parent) {
            $staff = SmStaff::find($request->staff_parent);
        }

        if ($staff && ! $guardians_relation) {
            if ($staff->gender_id == 1) {
                $guardians_relation = 'Father';
                $relation = 'F';
            } elseif ($staff->gender_id == 2) {
                $guardians_relation = 'Mother';
                $relation = 'M';
            } else {
                $guardians_relation = 'Other';
                $relation = 'O';
            }
        }

        $exit = SmParent::where('user_id', $staff->user_id)->first();
        if (! $exit) {
            $smParent = new SmParent();
            $smParent->user_id = $staff->user_id;
            if (($relation = 'F') !== '0') {
                $smParent->fathers_name = $request->fathers_name ?? $staff->full_name;
                $smParent->fathers_mobile = $request->fathers_phone ?? $staff->mobile;
                $smParent->fathers_occupation = $request->fathers_occupation;
                $smParent->fathers_photo = $staff->staff_photo;
            }

            if (($relation = 'M') !== '0') {
                $smParent->mothers_name = $request->mothers_name ?? $staff->full_name;
                $smParent->mothers_mobile = $request->mothers_phone ?? $staff->mobile;
                $smParent->mothers_occupation = $request->mothers_occupation;
                $smParent->mothers_photo = $staff->staff_photo;
            }

            $smParent->guardians_name = $request->guardians_name ?? $staff->full_name;
            $smParent->guardians_mobile = $request->guardians_phone ?? $staff->mobile;
            $smParent->guardians_email = $request->guardians_email ?? $staff->email;
            $smParent->guardians_occupation = $request->guardians_occupation;
            $smParent->guardians_relation = $guardians_relation;
            $smParent->relation = $relation;
            $smParent->guardians_photo = $staff->staff_photo;
            $smParent->guardians_address = $request->guardians_address;
            $smParent->school_id = Auth::user()->school_id;
            $smParent->academic_id = $request->session;
            $smParent->created_at = $academic_year->year.'-01-01 12:00:00';
            $smParent->save();

            return $smParent->id;
        }

        return $exit->id;
    }

    private function loginLogout($user_id): void
    {
        Auth::logout();
        Artisan::call('optimize:clear');
        Auth::loginUsingId($user_id);
    }

    private function loginSession($user_id)
    {
        Cache::forget('sidebars'.auth()->user()->id);
        userStatusChange($user_id, 0);
        Session::flush();
        Auth::logout();

        $logged_in = Auth::loginUsingId($user_id);
        if ($logged_in) {

            if (! Auth::user()->access_status) {
                $this->guard()->logout();
                Toastr::error('You are not allowed, Please contact with administrator.', 'Failed');

                return redirect()->route('login');
            }

            // System date format save in session
            $date_format_id = generalSetting()->date_format_id;
            $system_date_format = 'jS M, Y';
            if ($date_format_id) {
                $system_date_format = SmDateFormat::where('id', $date_format_id)->first(['format'])->format;
            }

            session()->put('system_date_format', $system_date_format);

            // System academic session id in session

            $all_modules = [];
            $modules = InfixModuleManager::select('name')->get();
            foreach ($modules as $module) {
                $all_modules[] = $module->name;
            }

            session()->put('all_module', $all_modules);

            // Session put text decoration
            $ttl_rtl = generalSetting()->ttl_rtl;
            session()->put('text_direction', $ttl_rtl);

            // session put academic years
            if (moduleStatusCheck('University')) {
                $academic_years = Auth::check() ? UnAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get() : '';
            } else {
                $academic_years = Auth::check() ? SmAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get() : '';
            }

            session()->put('academic_years', $academic_years);
            // session put sessions and selected language

            $profile = SmStaff::where('user_id', Auth::id())->first();
            if ($profile) {
                session()->put('profile', $profile->staff_photo);
            }

            $session_id = $profile && $profile->academic_id ? $profile->academic_id : generalSetting()->session_id;

            if (moduleStatusCheck('University')) {
                $session_id = generalSetting()->un_academic_id;
                if (! $session_id) {
                    $session = UnAcademicYear::where('school_id', Auth::user()->school_id)->where('active_status', 1)->first();
                } else {
                    $session = UnAcademicYear::find($session_id);
                }

                session()->put('sessionId', $session->id);
                session()->put('session', $session);
            } else {
                if (! $session_id) {
                    $session = SmAcademicYear::where('school_id', Auth::user()->school_id)->where('active_status', 1)->first();
                } else {
                    $session = SmAcademicYear::find($session_id);
                }

                session()->put('sessionId', $session->id);
                session()->put('session', $session);
            }

            if (! $session) {
                $session = SmAcademicYear::where('school_id', Auth::user()->school_id)->first();
            }

            session()->put('sessionId', $session->id);
            session()->put('session', $session);
            session()->put('school_config', generalSetting());

            $dashboard_background = DB::table('sm_background_settings')->where([['is_default', 1], ['title', 'Dashboard Background']])->first();
            session()->put('dashboard_background', $dashboard_background);

            $email_template = SmsTemplate::where('school_id', Auth::user()->school_id)->first();
            session()->put('email_template', $email_template);

            session(['role_id' => Auth::user()->role_id]);
            $agent = new Agent();
            $smUserLog = new SmUserLog();
            $smUserLog->user_id = Auth::user()->id;
            $smUserLog->role_id = Auth::user()->role_id;
            $smUserLog->school_id = Auth::user()->school_id;
            $smUserLog->ip_address = Request::ip();
            if (moduleStatusCheck('University')) {
                $smUserLog->un_academic_id = getAcademicid();
            } else {
                $smUserLog->academic_id = getAcademicid() ?? 1;
            }

            $smUserLog->user_agent = $agent->browser().', '.$agent->platform();
            $smUserLog->save();

            userStatusChange(auth()->user()->id, 1);

        }

        return null;
    }
}
