<?php

namespace App\Http\Controllers;

use App\Models\StudentRecord;
use App\SmClass;
use App\SmParent;
use App\SmSection;
use App\SmStaff;
use App\SmStudent;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\RolePermission\Entities\InfixRole;

class SmLoginAccessControlController extends Controller
{

    public function loginAccessControl()
    {

        try {
            $roles = InfixRole::where('id', '!=', 1)->where('id', '!=', 3)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->select(['name', 'id', 'type'])->get();
            $classes = SmClass::select(['id', 'class_name'])->get();

            return view('backEnd.systemSettings.login_access_control', ['roles' => $roles, 'classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function searchUser(Request $request)
    {

        $request->validate([
            'role' => 'required',
        ]);

        try {
            $login_user = auth()->user();
            $role = $request->role;
            $shift_id = $request->shift;
            $roles = InfixRole::where('is_saas', 0)->where('id', '!=', 1)->where('id', '!=', 3)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->select(['name', 'id', 'type'])->get();
            $classes = SmClass::select(['id', 'class_name'])->get();
            $students = SmStudent::query();
            $class = SmClass::select(['id', 'class_name'])->find($request->class);
            $section = SmSection::find($request->section);
            $records = StudentRecord::query();
            if ($request->role == 2) {
                if (moduleStatusCheck('University')) {
                    $records = universityFilter($records, $request)->where('is_promote', 0);
                    $student_ids = $records
                                ->when($request->shift, function ($query, $shift) {
                                    return $query->where('shift_id', $shift);
                                })
                                ->get('student_id')
                                ->toArray();
                    $students->whereIn('id', $student_ids);
                } else {

                    $students->with(['parents' => function ($query) {
                        return $query->select(['id', 'user_id']);
                    }, 'user' => function ($query) {
                        return $query->select(['access_status', 'id']);
                    }, 'parents.parent_user' => function ($query) {
                        return $query->select(['id', 'access_status']);
                    }, 'studentRecords' => function ($q) use ($request, $login_user) {
                        return $q->where('class_id', $request->class)->when($request->section, function ($q) use ($request): void {
                            $q->where('section_id', $request->section);
                        })->where('school_id', $login_user->school_id)->select(['school_id', 'section_id', 'class_id', 'id']);
                    }, 'studentRecords.class' => function ($query) {
                        return $query->select(['id', 'class_name']);
                    }, 'studentRecords.section' => function ($query) {
                        return $query->select(['section_name', 'id']);
                    }])->whereHas('studentRecords', function ($q) use ($request, $login_user) {
                        return $q->where('class_id', $request->class)->when($request->section, function ($q) use ($request): void {
                            $q->where('section_id', $request->section);
                        })->where('school_id', $login_user->school_id);
                    });
                }

                $students->where('active_status', 1)->where('school_id', auth()->user()->school_id);
                $students = $students->select(['admission_no', 'user_id', 'id', 'roll_no', 'first_name', 'last_name', 'full_name'])->get();

                return view('backEnd.systemSettings.login_access_control', ['students' => $students, 'role' => $role, 'roles' => $roles, 'classes' => $classes, 'class' => $class, 'section' => $section, 'shift_id' => $shift_id]);
            }

            if ($request->role == '3') {
                $parents = SmParent::with('parent_user')->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

                return view('backEnd.systemSettings.login_access_control', ['parents' => $parents, 'role' => $role, 'roles' => $roles, 'classes' => $classes]);
            }

            $staffs = SmStaff::with('staff_user', 'roles')->where(function ($q) use ($request): void {
                $q->where('role_id', $request->role)->orWhere('previous_role_id', $request->role);
            })->get();

            return view('backEnd.systemSettings.login_access_control', ['staffs' => $staffs, 'role' => $role, 'roles' => $roles, 'classes' => $classes]);
        } catch (Exception $exception) {
            dd($exception);
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function loginAccessPermission(Request $request)
    {

        try {
            $status = $request->status == 'on' ? 1 : 0;
            $user = User::find($request->id);
            $user->access_status = $status;
            $user->save();

            return response()->json(['status' => $request->status, 'users' => $user->access_status]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function loginPasswordDefault(Request $request)
    {
        try {
            $user = User::find($request->id);
            $user->password = Hash::make('123456');
            $r = $user->save();
            if ($r) {
                $data['op'] = true;
                $data['msg'] = 'Success';
            } else {
                $data['op'] = false;
                $data['msg'] = 'Failed';
            }

            Log::info($user);

            return response()->json($data);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error($exception->getMessage(), 'Failed');

            return redirect()->back();
        }
    }
}
