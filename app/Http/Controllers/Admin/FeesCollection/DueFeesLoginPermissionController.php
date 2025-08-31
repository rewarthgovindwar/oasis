<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use App\Http\Controllers\Controller;
use App\Models\DueFeesLoginPrevent;
use App\Models\StudentRecord;
use App\SmClass;
use App\SmStudent;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Modules\RolePermission\Entities\InfixRole;

class DueFeesLoginPermissionController extends Controller
{
    public function index()
    {
            $user = auth()->user();
            $roles = InfixRole::where('is_saas', 0)
                ->whereIn('id', [2, 3])
                ->where('school_id', $user->school_id)
                ->select(['id', 'name'])
                ->get();
            $classes = SmClass::where('school_id', $user->school_id)
                ->where('academic_id', getAcademicId())
                ->select(['id', 'class_name'])
                ->get();

            return view('backEnd.feesCollection.due_fees_login_permission', ['roles' => $roles, 'classes' => $classes]);

    }

    public function search(Request $request)
    {
            $user = auth()->user();
            $roles = InfixRole::where('is_saas', 0)->whereIn('id', [2, 3])->where('school_id', $user->school_id)->select(['id', 'name'])->get();
            $classes = SmClass::where('school_id', $user->school_id)->where('academic_id', getAcademicId())->select(['id', 'class_name'])->get();
            $records = StudentRecord::query();
            $records->where('is_promote', 0)->where('school_id', $user->school_id);
            $records->when(moduleStatusCheck('University') && $request->filled('un_academic_id'), function ($u_query) use ($request): void {
                $u_query->where('un_academic_id', $request->un_academic_id);
            }, function ($query) use ($request): void {
                $query->when($request->academic_year, function ($query) use ($request): void {
                    $query->where('academic_id', $request->academic_year);
                });
            })
                ->when(moduleStatusCheck('University') && $request->filled('un_faculty_id'), function ($u_query) use ($request): void {
                    $u_query->where('un_faculty_id', $request->un_faculty_id);
                }, function ($query) use ($request): void {
                    $query->when($request->class_id, function ($query) use ($request): void {
                        $query->where('class_id', $request->class_id);
                    });
                })
                ->when(moduleStatusCheck('University') && $request->filled('un_department_id'), function ($u_query) use ($request): void {
                    $u_query->where('un_department_id', $request->un_department_id);
                }, function ($query) use ($request): void {
                    $query->when($request->section_id, function ($query) use ($request): void {
                        $query->where('section_id', $request->section_id);
                    });
                })
                ->when(! $request->academic_year && moduleStatusCheck('University') == false, function ($query): void {
                    $query->where('academic_id', getAcademicId());
                })
                ->when(moduleStatusCheck('University') && $request->filled('un_session_id'), function ($query) use ($request): void {
                    $query->where('un_session_id', $request->un_session_id);
                })
                ->when(moduleStatusCheck('University') && $request->filled('un_semester_label_id'), function ($query) use ($request): void {
                    $query->where('un_semester_label_id', $request->un_semester_label_id);
                });
            $student_records = $records->where('is_promote', 0)->whereHas('student')->get(['student_id'])->unique('student_id')->toArray();
            $all_students = SmStudent::with([
                'studentRecords' => function ($query): void {
                    $query->select(['id', 'class_id', 'section_id', 'student_id']);
                },
                'parents' => function ($query): void {
                    $query->select('id', 'user_id', 'fathers_name');
                },
                'studentRecords.class' => function ($query): void {
                    $query->select('id', 'class_name');
                },
                'studentRecords.section' => function ($query): void {
                    $query->select('id', 'section_name');
                },
                'parents.parent_user' => function ($query): void {
                    $query->select('id', 'full_name', 'role_id');
                },
                'gender' => function ($query): void {
                    $query->select('id', 'base_setup_name');
                },
            ])
                ->whereIn('id', $student_records)
                ->where('active_status', 1)
                ->when($request->filled('admission_no'), function ($query) use ($request): void {
                    $query->where('admission_no', $request->admission_no);
                })
                ->when($request->name, function ($query) use ($request): void {
                    $query->where('full_name', 'like', '%'.$request->name.'%');
                });

            $students = $all_students->select(['user_id', 'id', 'admission_no', 'roll_no', 'first_name', 'last_name', 'parent_id'])->get();

            return view('backEnd.feesCollection.due_fees_login_permission', ['roles' => $roles, 'classes' => $classes, 'students' => $students]);

    }

    public function store(Request $request){
        /*
        try{
        */
            $user_id = $request->id;
            $status = $request->status;
            if ($user_id) {
                $user = User::find($user_id);
                $checkExist = DueFeesLoginPrevent::where('user_id', $user->id)->delete();
                if ($user && $status == 'on') {
                    $dueFeesLoginPrevent = new DueFeesLoginPrevent();
                    $dueFeesLoginPrevent->user_id = $user->id;
                    $dueFeesLoginPrevent->role_id = $user->role_id;
                    $dueFeesLoginPrevent->school_id = $user->school_id;
                    $dueFeesLoginPrevent->academic_id = getAcademicId();
                    $dueFeesLoginPrevent->save();
                }
            }

            return response()->json(['status' => $request->status, 'users' => $request->id]);
        /*
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
