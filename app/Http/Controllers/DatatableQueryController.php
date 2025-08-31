<?php

namespace App\Http\Controllers; 

use Exception;
use Throwable;
use App\SmBook;
use App\SmItem;
use DataTables;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmUserLog;
use App\SmHomework;
use App\SmAddIncome;
use App\SmBookIssue;
use App\SmComplaint;
use App\SmAddExpense;
use App\SmEmailSmsLog;
use App\SmFeesPayment;
use App\SmItemReceive;
use App\SmLeaveDefine;
use DateTimeImmutable;
use App\SmAcademicYear;
use App\SmClassTeacher;
use App\SmLeaveRequest;
use App\SmNotification;
use App\SmAssignSubject;
use App\SmBankPaymentSlip;
use App\Models\FeesInvoice;
use App\SmStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmAssignClassTeacher;
use App\SmTeacherUploadContent;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Scopes\ActiveStatusSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;

class DatatableQueryController extends Controller
{
    public function studentDetailsDatatable(Request $request)
    {
        Validator::make($request->all(), [
            'academic_year' => 'required',
        ]);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            $sessions = SmAcademicYear::where('school_id', Auth::user()->school_id)->get();
            $academic_year = $request->academic_year;
            $class_id = $request->class_id;
            $shift_id = shiftEnable() ? $request->shift : null;
            $name = $request->name;
            $roll_no = $request->roll_no;
            $section_id = $request->section_id;
            $data['un_session_id'] = $request->un_session_id;
            $data['un_academic_id'] = $request->un_academic_id;
            $data['un_faculty_id'] = $request->un_faculty_id;
            $data['un_department_id'] = $request->un_department_id;
            $data['un_semester_id'] = $request->un_semester_id;
            $data['un_semester_label_id'] = $request->un_semester_label_id;
            $data['un_section_id'] = $request->un_section_id;
            
            return view('backEnd.studentInformation.student_details', ['classes' => $classes, 'class_id' => $class_id, 'name' => $name, 'roll_no' => $roll_no, 'sessions' => $sessions, 'section_id' => $section_id, 'academic_year' => $academic_year, 'data' => $data, 'shift_id' => $shift_id]);
        }

        if ($request->ajax()) {
            $user = auth()->user();
            $records = StudentRecord::query();
            $records->where('is_promote', 0)->where('school_id', $user->school_id);
            if (moduleStatusCheck('University')) {
                $records->when($request->filled('un_academic_id'), function ($u_query) use ($request): void {
                    $u_query->where('un_academic_id', $request->un_academic_id);
                })
                    ->when($request->filled('un_faculty_id'), function ($u_query) use ($request): void {
                        $u_query->where('un_faculty_id', $request->un_faculty_id);
                    })
                    ->when($request->filled('un_department_id'), function ($u_query) use ($request): void {
                        $u_query->where('un_department_id', $request->un_department_id);
                    })
                    ->when($request->filled('un_session_id'), function ($query) use ($request): void {
                        $query->where('un_session_id', $request->un_session_id);
                    })
                    ->when($request->filled('un_semester_label_id'), function ($query) use ($request): void {
                        $query->where('un_semester_label_id', $request->un_semester_label_id);
                    });

            } else {
                $records->when(!$request->academic_year, function ($query): void {
                    $query->where('academic_id', getAcademicId());
                }, function ($query) use ($request): void {
                    $query->where('academic_id', $request->academic_year);
                })->when($request->section, function ($query) use ($request): void {
                    $query->where('section_id', $request->section);
                })->when($request->shift_id, function ($query) use ($request) {
                    $query->where('shift_id', $request->shift_id);
                })->when($request->class, function ($query) use ($request): void {
                    $query->where('class_id', $request->class);
                });
            }

            $roll_no = $request->roll_no;

            if (generalSetting()->multiple_roll && $roll_no) {
                $records->where('roll_no', 'like', '%' . $roll_no . '%');
            }


            $student_records = $records->whereHas('student')->get(['student_id'])->unique('student_id')->toArray();
            $all_students = SmStudent::with('studentRecords', 'studentRecords.class', 'studentRecords.section', 'studentRecords.shift')->whereIn('id', $student_records)
                ->where('active_status', 1)
                ->with(['parents' => function ($query): void {
                    $query->select('id', 'fathers_name');
                }])
                ->with(['gender' => function ($query): void {
                    $query->select('id', 'base_setup_name');
                }])
                ->with(['category' => function ($query): void {
                    $query->select('id', 'category_name');
                }])
                ->when($request->name, function ($query) use ($request): void {
                    $query->where('full_name', 'like', '%' . $request->name . '%');
                });

            if (!generalSetting()->multiple_roll && $roll_no) {
                $all_students->where('roll_no', 'like', '%' . $roll_no . '%');
            }
            if ($user->role_id == 4 && !userPermission('student.show-all-student')) {
                $classTeacher = SmClassTeacher::where('teacher_id', auth()->user()->staff->id)->pluck('assign_class_teacher_id');
                $assignClassTeacher = SmAssignClassTeacher::whereIn('id', $classTeacher)->get();

                $all_students = $all_students->whereHas('studentRecords', function ($q) use ($assignClassTeacher): void {
                    $q->whereIn('class_id', $assignClassTeacher->pluck('class_id'))
                        ->whereIn('section_id', $assignClassTeacher->pluck('section_id'));
                });
            }

            return DataTables::of($all_students)
                ->addIndexColumn()
                ->addColumn('dob', function ($row) {
                    return dateConvert(@$row->date_of_birth);
                })
                ->addColumn('full_name', function ($row): string {
                    return '<a target="_blank" href="' . route('student_view', [$row->id]) . '">' . $row->first_name . ' ' . $row->last_name . '</a>';
                })
                ->addColumn('mobile', function ($row): string {
                    return '<a href="tel:' . $row->mobile . '">' . $row->mobile . '</a>';
                })
                ->addColumn('semester_label', function ($row) {
                    $semester_label = [];
                    foreach ($row->studentRecords as $label) {
                        if (moduleStatusCheck('University')) {
                            $semester_label[] = $label->unSemesterLabel->name;
                        }
                    }

                    return $semester_label;
                })
                ->addColumn('class_sec', function ($row): string {
                    $class_sec = [];
                    foreach ($row->studentRecords as $classSec) {
                        if (moduleStatusCheck('University')) {
                            $class_sec[] = $classSec->unFaculty->name . '(' . $classSec->unDepartment->name . ')';
                        } elseif(shiftEnable()) {
                            $class_sec[] = $classSec->class->class_name . '(' . $classSec->section->section_name . ')[ '.@$classSec->shift->name.' ] <br>';
                        }else{
                            $class_sec[] = $classSec->class->class_name . '(' . $classSec->section->section_name . ')<br>';
                        }
                    }

                    return implode(', ', $class_sec);
                })
                ->addColumn('action', function ($row): string {
                    $langName = (moduleStatusCheck('University')) ? app('translator')->get('university::un.assign_faculty_department') : app('translator')->get('student.assign_class');

                    return '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('student.assign-class') ? '<a class="dropdown-item" target="_blank" href="' . route('student.assign-class', [$row->id]) . '">' . $langName . '</a>' : '')

                        . ((userPermission('student_view') && moduleStatusCheck('University')) ?
                            '<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id, 'assign_subject']) . '">' . app('translator')->get('common.view') . '</a>' : '')
                        .(userPermission('student_view') ? '<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' :'')
                        
                        .(userPermission('student_edit') ? '<a class="dropdown-item" href="' . route('student_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                        (userPermission('student_edit') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.disable') . '</a>') : '') .

                        '</div>
                                </div>';
                })
                ->rawColumns(['action', 'full_name', 'mobile', 'dob', 'class_sec', 'full_name', 'mobile', 'dob', 'class_sec'])
                ->make(true);
        }

        return view('backEnd.studentInformation.students');
    }

    public function searchStudentList(Request $request)
    {
        $student_ids = StudentRecord::when($request->academic_year, function ($query) use ($request): void {
            $query->where('academic_id', $request->academic_year);
        })
            ->when($request->class, function ($query) use ($request): void {
                $query->where('class_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request): void {
                $query->where('section_id', $request->section);
            })
            ->when(!$request->academic_year, function ($query): void {
                $query->where('academic_id', getAcademicId());
            })
            ->distinct('student_id')->pluck('student_id')->toArray();

        $students = SmStudent::query();
        $students->where('active_status', 1);

        if ($request->name != '') {
            $students->where('full_name', 'like', '%' . $request->name . '%');
        }

        if ($request->roll_no != '') {
            $students->where('roll_no', 'like', '%' . $request->roll_no . '%');
        }

        $students = $students->whereIn('id', $student_ids)->where('school_id', Auth::user()->school_id)
            ->with(['parents' => function ($query): void {
                $query->select('id', 'fathers_name');
            }])
            ->with(['gender' => function ($query): void {
                $query->select('id', 'base_setup_name');
            }])
            ->with(['category' => function ($query): void {
                $query->select('id', 'category_name');
            }]);

        return DataTables::of($students)
            ->addIndexColumn()
            ->addColumn('dob', function ($row) {

                return dateConvert(@$row->date_of_birth);
            })
            ->rawColumns(['dob'])
            ->editColumn('full_name', function ($row): string {
                return '<a target="_blank" href="' . route('student_view', [$row->id]) . '">' . $row->first_name . ' ' . $row->last_name . '</a>';
            })
            ->editColumn('mobile', function ($row): string {
                return '<a href="tel:' . $row->mobile . '">' . $row->mobile . '</a>';
            })
            ->addColumn('class_sec', function ($row) use ($request) {
                $class_sec = [];
                foreach ($row->studentRecords as $classSec) {
                    $class_sec[] = $classSec->class->class_name . '(' . $classSec->section->section_name . '), ';
                }

                if ($request->class) {
                    $sections = [];
                    $class = $row->recordClass ? $row->recordClass->class->class_name : '';
                    if ($request->section) {
                        $sections[] = $row->recordSection != '' ? $row->recordSection->section->section_name : '';
                    } else {
                        foreach ($row->recordClasses as $section) {
                            $sections[] = $section->section->section_name;
                        }
                    }

                    return $class . '(' . $sections . '), ';
                }

                return $class_sec;
            })
            ->addColumn('action', function ($row): string {
                return '<div class="dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                    (userPermission('student_edit') ? '<a class="dropdown-item" href="' . route('student_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                    (userPermission('disabled_student') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                        '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.disable') . '</a>') : '') .

                    '</div>
                            </div>';
            })
            ->rawColumns(['action', 'full_name', 'mobile', 'dob', 'class_sec', 'mobile', 'dob', 'class_sec'])
            ->make(true);
    }

    public function AjaxStudentSearch($class, $section, $date)
    {

            // $date = $request->attendance_date;
            if (getClassActeacherAccesscess()) {
                $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            } else {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.class_id')
                    ->where('sm_assign_subjects.academic_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.school_id', Auth::user()->school_id)
                    ->select('sm_classes.id', 'class_name')
                    ->distinct('sm_classes.id')
                    ->get();
            }

            $students = SmStudent::where('class_id', $class)->where('section_id', $section)->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)->get();

            if ($students->isEmpty()) {
                Toastr::error('No Result Found', 'Failed');

                return redirect('student-attendance');
            }

            $already_assigned_students = [];
            $new_students = [];
            $attendance_type = '';
            foreach ($students as $student) {
                $attendance = SmStudentAttendance::where('student_id', $student->id)
                    ->where('attendance_date', date('Y-m-d', $date))
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                if ($attendance != '') {
                    $already_assigned_students[] = $attendance;
                    $attendance_type = $attendance->attendance_type;
                } else {
                    $new_students[] = $student;
                }
            }

            $class_id = $class;
            $section_id = $section;
            $class_info = SmClass::find($class);
            $section_info = SmSection::find($section);

            $search_info['class_name'] = $class_info->class_name;
            $search_info['section_name'] = $section_info->section_name;
            $search_info['date'] = $date;

            $all_students = [];
            foreach ($already_assigned_students as $already_assigned_student) {
                $all_students[$already_assigned_student->student_id]['std_id'] = $already_assigned_student->student_id;
                $all_students[$already_assigned_student->student_id]['admission_no'] = $already_assigned_student->studentInfo->admission_no;
                $all_students[$already_assigned_student->student_id]['roll_no'] = $already_assigned_student->studentInfo->roll_no;
                $all_students[$already_assigned_student->student_id]['full_name'] = $already_assigned_student->studentInfo->full_name;
                $all_students[$already_assigned_student->student_id]['attendance_type'] = $already_assigned_student->attendance_type;
                $all_students[$already_assigned_student->student_id]['notes'] = $already_assigned_student->notes;
                $all_students[$already_assigned_student->student_id]['attendance_date'] = $already_assigned_student->attendance_date;
            }

            foreach ($new_students as $new_student) {
                $all_students[$new_student->id]['std_id'] = $new_student->id;
                $all_students[$new_student->id]['admission_no'] = $new_student->admission_no;
                $all_students[$new_student->id]['roll_no'] = $new_student->roll_no;
                $all_students[$new_student->id]['full_name'] = $new_student->full_name;
                $all_students[$new_student->id]['attendance_type'] = '';
                $all_students[$new_student->id]['notes'] = '';
                $all_students[$new_student->id]['attendance_date'] = '';
            }

            // return $all_students;

            // if ($request->ajax()) {

            return DataTables::of($all_students)
                ->addIndexColumn()
                ->addColumn('teacher_note', function ($row): string {
                    return '<input type="text" name="note>';
                })
                // ->rawColumns(['teacher_note'])

                ->addColumn('action', function (array $row): string {

                    return '<div class="d-flex radio-btn-flex">
                                    <div class="mr-20">
                                        <input type="radio" data-id="' . $row['std_id'] . '" name="attendance[' . $row['std_id'] . ']" id="attendanceP' . $row['std_id'] . '"' . ($row['attendance_type'] == 'P' ? 'checked' : '') . ' value="P" class="common-radio attendanceP attendance_type">
                                        <label for="attendanceP' . $row['std_id'] . '">' . app('translator')->get('common.present') . '</label>
                                    </div>
                                    <div class="mr-20">
                                        <input type="radio" data-id="' . $row['std_id'] . '" name="attendance[' . $row['std_id'] . ']" id="attendanceL' . $row['std_id'] . '"' . ($row['attendance_type'] == 'L' ? 'checked' : '') . ' value="L" class="common-radio attendanceL attendance_type">
                                        <label for="attendanceL' . $row['std_id'] . '">' . app('translator')->get('common.late') . '</label>
                                    </div>
                                    <div class="mr-20">
                                        <input type="radio" data-id="' . $row['std_id'] . '" name="attendance[' . $row['std_id'] . ']" id="attendanceA' . $row['std_id'] . '"' . ($row['attendance_type'] == 'A' ? 'checked' : '') . ' value="A" class="common-radio attendanceA attendance_type">
                                        <label for="attendanceA' . $row['std_id'] . '">' . app('translator')->get('common.absent') . '</label>
                                    </div>
                                    <div class="mr-20">
                                        <input type="radio" data-id="' . $row['std_id'] . '" name="attendance[' . $row['std_id'] . ']" id="attendanceF' . $row['std_id'] . '"' . ($row['attendance_type'] == 'F' ? 'checked' : '') . ' value="F" class="common-radio attendanceF attendance_type">
                                        <label for="attendanceF' . $row['std_id'] . '">' . app('translator')->get('common.half_day') . '</label>
                                    </div>
                                       
    
                                    </div>';
                })
                ->rawColumns(['action', 'teacher_note'])
                ->make(true);

            // }


            return view('backEnd.studentInformation.student_attendance', compact('classes', 'date', 'class_id', 'section_id', 'date', 'already_assigned_students', 'new_students', 'attendance_type', 'search_info'));
    }

    public function getStaffList(Request $request)
    {

            if (Auth::user()->role_id == 1) {
                $staffs = SmStaff::query();

                $staffs->withOutGlobalScope(ActiveStatusSchoolScope::class)->where('school_id', Auth::user()->school_id)
                    ->where('is_saas', 0)
                    ->when(moduleStatusCheck('SaasHr'), function ($query) {
                        return $query->where('custom_saas_user', '!=', 1);
                    })
                    ->with(['roles' => function ($query): void {
                        $query->select('id', 'name');
                    }])
                    ->with(['departments' => function ($query): void {
                        $query->select('id', 'name');
                    }])
                    ->with(['designations' => function ($query): void {
                        $query->select('id', 'title');
                    }]);
                if ($request->role_id) {
                    $staffs->where(function ($q) use ($request): void {
                        $q->where('role_id', $request->role_id)->orWhere('previous_role_id', $request->role_id);
                    });

                }

                if ($request->staff_no) {
                    $staffs->where('staff_no', $request->staff_no);
                }

                if ($request->staff_name) {
                    $staffs->where('full_name', 'like', '%' . $request->staff_name . '%');
                }

                if (Auth::user()->role_id != 1) {

                    $staffs->where('role_id', '!=', 1);
                }

            } else {
                $staffs = SmStaff::where('is_saas', 0)
                    ->where('school_id', Auth::user()->school_id)
                    ->when(moduleStatusCheck('SaasHr'), function ($query) {
                        return $query->where('custom_saas_user', '!=', 1);
                    })
                    ->where('role_id', '!=', 1)
                    ->where('role_id', '!=', 5)
                    ->with([
                        'roles' => function ($query): void {
                            $query->select('id', 'name');
                        },
                        'departments' => function ($query): void {
                            $query->select('id', 'name');
                        },
                        'designations' => function ($query): void {
                            $query->select('id', 'title');
                        },
                    ]);
            }

            return DataTables::of($staffs)
                ->addIndexColumn()
                ->addColumn('switch', function ($row): string {
                    if (Auth::user()->id != $row->user_id || Auth::user()->role_id != 1) {
                        return '<label class="switch_toggle">
                            <input type="checkbox" id="' . $row->id . '" value="' . $row->id . '" class="switch-input-staff hr_' . $row->id . '" ' . ($row->active_status == 0 ? '' : 'checked') . '>
                            <span class="slider round"></span>
                          </label>';
                    }

                    return '';
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">'.

                        ( userPermission('viewStaff') ? '<a class="dropdown-item" target="_blank" href="' . route('viewStaff', [$row->id]) . '">'.app('translator')->get('common.view').'</a>' : '' ).
                        (userPermission('editStaff') ? '<a class="dropdown-item" href="' . route('editStaff', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                        (userPermission('deleteStaff') ? ($row->user_id == Auth::id() ? '' :
                            '<a onclick="deleteStaff(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .

                        '</div>
                                </div>';
                })
                ->rawColumns(['action', 'switch'])
                ->make(true);


    }

    public function incomeList(Request $request)
    {
        $builder = SmAddIncome::with('incomeHeads', 'paymentMethod')->where('active_status', '=', 1)->where('school_id', Auth::user()->school_id);

        return DataTables::of($builder)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {

                return dateConvert(@$row->created_at);
            })
            ->rawColumns(['date'])
            ->addColumn('action', function ($row): string {
                return '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                    (userPermission('student_edit') ? '<a class="dropdown-item" href="' . route('student_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                    (userPermission('disabled_student') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                        '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.disable') . '</a>') : '') .

                    '</div>
                                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);

    }

    public function emailSmsLogAjax()
    {
        $emailSmsLogs = SmEmailSmsLog::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id);

        return DataTables::of($emailSmsLogs)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return dateConvert(@$row->created_at);
            })
            ->addColumn('send_via', function ($row): string {
                if ($row->send_through == 'E') {
                    return 'Email';
                }

                return 'Sms';
            })
            ->rawColumns(['date'])
            ->make(true);
    }

    public function userLogAjax(Request $request)
    {

        $user_logs = SmUserLog::select('sm_user_logs.*')->where('academic_id', getAcademicId())
            ->where('sm_user_logs.school_id', Auth::user()->school_id)
            ->orderBy('id', 'desc')
            ->with(['role' => function ($query): void {
                $query->select('id', 'name');
            }])
            ->with(['user' => function ($query): void {
                $query->select('id', 'full_name');
            }]);

        return DataTables::of($user_logs)
            ->addIndexColumn()
            // filter role.name
            ->filterColumn('role.name', function ($query, $keyword): void {
                $query->whereHas('role', function ($q) use ($keyword): void {
                    $q->where('name', 'like', sprintf('%%%s%%', $keyword));
                });
            })
            ->orderColumn('role.name', function ($query, $keyword): void {
                $query->orderBy('infix_roles.name', $keyword)->orderBy('infix_roles.name', 'DESC');
            })
            ->addColumn('date', function ($row) {
                return dateConvert(@$row->created_at);
            })
            ->rawColumns(['date'])
            ->addColumn('login_time', function ($row) {
                return $row->created_at->toDayDateTimeString();
            })
            ->filterColumn('login_time', function ($query, $keyword): void {
                $date = date('Y-m-d', strtotime($keyword));

                if ($date != '0') {
                    $query->whereDate('created_at', '=', $date);
                }
            })
            ->orderColumn('login_time', function ($query, $keyword): void {
                $query->orderBy('created_at', $keyword);
            })
            ->rawColumns(['login_time'])
            ->make(true);
    }

    public function bankPaymentSlipAjax(Request $request)
    {
            $bank_slips = SmBankPaymentSlip::query();
            if (moduleStatusCheck('University')) {
                $bank_slips->where('un_academic_id', getAcademicId());
                if ($request->un_semester_label_id != '') {
                    $bank_slips->where('un_semester_label_id', $request->un_semester_label_id);
                }
            } else {
                $bank_slips->where('academic_id', getAcademicId());
                if ($request->class != '') {
                    $bank_slips->where('class_id', $request->class);
                }

                if ($request->section != '') {
                    $bank_slips->where('section_id', $request->section);
                }

                if ($request->payment_date != '') {
                    $date = strtotime($request->payment_date);
                    $new_format = date('Y-m-d', $date);
                    $bank_slips->where('date', $new_format);
                }
            }

            if ($request->approve_status != '') {
                $bank_slips->where('approve_status', $request->approve_status);
            }

            $bank_slips = $bank_slips->with('studentInfo', 'installmentAssign.installment', 'feesType')
                ->where('school_id', Auth::user()->school_id)
                ->where('approve_status', 0)
                ->orderBy('id', 'desc');

            // return $bank_slips->get();
            return DataTables::of($bank_slips)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return dateConvert(@$row->created_at);
                })
                ->rawColumns(['date'])
                ->addColumn('status', function ($row): string {
                    if ($row->approve_status == 0) {
                        $btn = '<button class="primary-btn small bg-warning text-white border-0">' . app('translator')->get('common.pending') . '</button>';
                    } elseif ($row->approve_status == 1) {
                        $btn = '<button class="primary-btn small bg-success text-white border-0  tr-bg">' . app('translator')->get('common.approved') . '</button>';
                    } elseif ($row->approve_status == 2) {
                        $btn = '<button class="primary-btn small bg-danger text-white border-0  tr-bg">' . app('translator')->get('common.rejected') . '</button>';
                    }

                    return $btn;
                })
                ->addColumn('p_amount', function ($row): string {
                    return generalSetting()->currency_symbol . ' ' . $row->amount;
                })
                ->addColumn('slip', function ($row) {
                    if (!empty($row->slip)) {
                        return '<a class="text-color" data-toggle="modal" data-target="#showCertificateModal(' . $row->id . ');" href="#">' . app('translator')->get('common.approve') . '</a>';

                    }

                })
                ->addColumn('action', function ($row): string {
                    if ($row->approve_status == 0) {
                        $btn = '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a onclick="enableId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#enableStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.approve') . '</a>' .
                            '<a onclick="rejectPayment(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-id="' . $row->id . '"  >' . app('translator')->get('common.reject') . '</a>' .
                            '</div>
                                </div>';
                    } elseif ($row->approve_status == 1) {
                        $btn = '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#">' . app('translator')->get('common.approved') . '</a>' .
                            '</div>
                                </div>';
                    } elseif ($row->approve_status == 2) {
                        $btn = '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a onclick="viewReason(' . $row->id . ');" class="dropdown-item ' . 'reason' . $row->id . '" href="#" data-reason="' . $row->reason . '"  >' . app('translator')->get('common.view') . '</a>' .
                            '</div>
                                </div>';
                    }

                    return $btn;
                })
                ->rawColumns(['status', 'action', 'slip'])
                ->make(true);
    }


    public function assignmentList()
    {

        $user = Auth()->user();

        if (teacherAccess()) {
            SmNotification::where('user_id', $user->id)->where('role_id', 1)->update(['is_read' => 1]);
        }

        if (!teacherAccess()) {

            $uploadContents = SmTeacherUploadContent::where('content_type', 'as')
                ->where('school_id', $user->school_id)
                ->whereNullLms();

        } else {
            $uploadContents = SmTeacherUploadContent::where(function ($q): void {
                $q->where('created_by', Auth::user()->id)->orWhere('available_for_admin', 1);
            })->where('content_type', 'as')->whereNullLms()
                ->where('school_id', $user->school_id);
        }

        if(moduleStatusCheck('University')){
            $uploadContents = $uploadContents->where('un_academic_id', getAcademicId())->with(['semesterLabel' => function ($query) {
                return $query->select(['id', 'name']);
            }, 'unAcademic' => function ($query) {
                return $query->select(['id', 'name']);
            }, 'unDepartment' => function ($query) {
                return $query->select(['id', 'name']);
            }, 'unSection' => function ($query) {
                return $query->select(['id', 'section_name']);
            }])->select([
                'id',
                'content_title',
                'upload_date',
                'content_type',
                'upload_file',
                'description',
                'available_for_admin',
                'available_for_all_classes',
                'un_academic_id',
                'un_semester_label_id',
                'un_department_id',
                'un_section_id',
            ]);
        } else{
            $uploadContents = $uploadContents->where('academic_id', getAcademicId())->with(['classes' => function ($query) {
                return $query->select(['id', 'class_name']);
            }, 'sections' => function ($query) {
                return $query->select(['id', 'section_name']);
            }])->select([
                'id',
                'content_title',
                'upload_date',
                'content_type',
                'upload_file',
                'description',
                'available_for_admin',
                'available_for_all_classes',
                'class',
                'section',
                'shift_id',
            ]);
        }

        return DataTables::of($uploadContents)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {

                return dateConvert(@$row->upload_date);
            })
            ->addColumn('type', function ($row) {
                if ($row->content_type == 'as') {
                    $type = 'assignment';
                }

                return __('study.' . $type);

            })
            ->addColumn('avaiable', function ($row): string {
                $avaiable = '';
                if ($row->available_for_admin == 1) {
                    $avaiable .= app('translator')->get('study.all_admins') . ', ';
                }

                if ($row->available_for_all_classes == 1) {
                    $avaiable .= app('translator')->get('study.all_classes_student') . ', ';
                }



                if (moduleStatusCheck('University')) {
                    $avaiable .= app('translator')->get('study.all_students_of') . ' ' . @$row->semesterLabel->name . '(' . @$row->unSection->section_name . '-' . @$row->undepartment->name . ')';
                } else{
                    if ($row->classes && $row->sections) {
                        if(shiftEnable()){
                            $avaiable .= (app('translator')->get('study.all_students_of') . " " . $row->classes->class_name . '->' . @$row->sections->section_name).', ->'. @$row->shift->name;
                        }else{
                            $avaiable .= (app('translator')->get('study.all_students_of') . " " . $row->classes->class_name . '->' . @$row->sections->section_name);
                        }
                    }

                    if ($row->classes && !$row->section) {
                        $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . app('translator')->get('study.all_sections')) . ', ';
                    }
                }

                return $avaiable;

            })
            ->addColumn('class_sections', function ($row) {
                if (moduleStatusCheck('University')) {
                    $semLabel = $row->semesterLabel->name;
                    $academ = $row->unAcademic->name;

                    return $semLabel . '(' . $academ . ')';
                }

                if (($row->class) && ($row->section)) {
                    $classes = $row->classes->class_name;
                    $sections = $row->sections->section_name;
                    if(shiftEnable())
                    {
                        return $classes . '(' . $sections . ')' . '-' . $row->shift->name;
                    }else{
                        return $classes . '(' . $sections . ')' ;
                    }
                }

                if ($row->class && !$row->section) {
                    $classes = $row->classes->class_name;
                    $nullsections = app('translator')->get('common.all_sections');

                    return $classes . '(' . $nullsections . ')';
                }

                if ($row->section) {
                    return  $row->sections->section_name;
                }

                if ($row->class) {
                    return $row->classes->class_name;
                }

            })
            ->rawColumns(['date'])
            ->addColumn('action', function ($row): string {
                $btn = '<div class="dropdown CRM_dropdown">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">'
                                . app('translator')->get('common.select') .
                            '</button>
                            <div class="dropdown-menu dropdown-menu-right">';
                if (userPermission('assignment-list-view')) {
                    $btn .= '<a data-modal-size="modal-lg" title="' . __('study.view_content_details') . '" class="dropdown-item modalLink" href="' . route('upload-content-view', [$row->id]) . '">'
                                . app('translator')->get('common.view') .
                            '</a>';
                }

                if (userPermission('assignment-list-edit')) {
                    $btn .= '<a class="dropdown-item" href="' . route('upload-content-edit', [$row->id]) . '">'
                                . app('translator')->get('common.edit') .
                            '</a>';
                }

                if (userPermission('assignment-list-delete')) {
                    if (Config::get('app.app_sync')) {
                        $btn .= '<span data-toggle="tooltip" title="Disabled For Demo ">
                                    <a class="dropdown-item" href="#">' . app('translator')->get('common.disable') . '</a>
                                </span>';
                    } else {
                        $btn .= '<a onclick="deleteAssignMent(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteApplyLeaveModal" data-id="' . $row->id . '">'
                                    . app('translator')->get('common.delete') .
                                '</a>';
                    }
                }

                if (userPermission('assignment-list-download') && $row->upload_file) {
                    $btn .= '<a class="dropdown-item" href="' . url($row->upload_file) . '" download>'
                                . __('common.download') . ' <span class="pl ti-download"></span>
                            </a>';
                }

                $btn .= '</div></div>';

                return $btn;
            })
            ->rawColumns(['action', 'date'])

            ->make(true);
    }

    public function leaveDefineList()
    {



            $leave_defines = SmLeaveDefine::with([
                'role', 'user', 'leaveType',
            ])->where('academic_id', getAcademicId())
                ->select(['sm_leave_defines.*']);
                
            return DataTables::of($leave_defines->get())
                ->addColumn('leave_type', function ($row) {
                    return $row->leaveType?->type;
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                        <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                        <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('leave-define-edit') ? '<a class="dropdown-item" href="' . route('leave-define-edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                        (userPermission('leave-define-edit') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="addLeaveDay(' . $row->id . ');"  class="dropdown-item ' . 'reason' . $row->id . '" href="#" data-toggle="modal" data-target="#addLeaveDayModal" data-total_days="' . $row->days . '"  >' . app('translator')->get('common.add_days') . '</a>') : '') .

                        (userPermission('leave-define-delete') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteLeaveDefine(' . $row->id . ');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteLeaveDefineModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .
                        '</div>
                    </div>';

                return $btn;
            })
            ->rawColumns(['action', 'userName', 'leave_type'])
            ->make(true);

    }

    public function syllabusList()
    {
            $user = Auth::user();
            if (!teacherAccess()) {
                $uploadContents = SmTeacherUploadContent::where('content_type', 'sy')
                    ->whereNullLms()
                    ->where('school_id', $user->school_id);
            } else {
                $uploadContents = SmTeacherUploadContent::where(function ($q) use ($user): void {
                    $q->where('created_by', $user->id)->orWhere('available_for_admin', 1);
                })->where('content_type', 'sy')
                    ->whereNullLms()
                    ->where('school_id', $user->school_id);
            }


            if(moduleStatusCheck('University')){
                $uploadContents = $uploadContents->where('un_academic_id', getAcademicId())->with(['semesterLabel' => function ($query) {
                    return $query->select(['id', 'name']);
                }, 'unAcademic' => function ($query) {
                    return $query->select(['id', 'name']);
                }, 'unDepartment' => function ($query) {
                    return $query->select(['id', 'name']);
                }, 'unSection' => function ($query) {
                    return $query->select(['id', 'section_name']);
                }])->select([
                    'id',
                    'content_title',
                    'upload_date',
                    'content_type',
                    'upload_file',
                    'description',
                    'available_for_admin',
                    'available_for_all_classes',
                    'un_academic_id',
                    'un_semester_label_id',
                    'un_department_id',
                    'un_section_id',
                ]);
            } else{
                $uploadContents = $uploadContents->where('academic_id', getAcademicId())->with(['classes' => function ($query) {
                    return $query->select(['id', 'class_name']);
                }, 'sections' => function ($query) {
                    return $query->select(['id', 'section_name']);
                }])->select([
                    'id',
                    'content_title',
                    'upload_date',
                    'content_type',
                    'upload_file',
                    'description',
                    'available_for_admin',
                    'available_for_all_classes',
                    'class',
                    'section',
                    'shift_id'
                ]);
            }


            // return  $uploadContents;
            return DataTables::of($uploadContents)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {

                    return dateConvert(@$row->upload_date);
                })
                ->addColumn('type', function ($row) {
                    if ($row->content_type == 'as') {
                        $type = 'assignment';
                    } elseif ($row->content_type == 'st') {
                        $type = 'study_material';
                    } elseif ($row->content_type == 'sy') {
                        $type = 'syllabus';
                    } else {
                        $type = 'others';
                    }

                    return __('study.' . $type);

                })
                ->addColumn('avaiable', function ($row): string {
                    $avaiable = '';
                    if ($row->available_for_admin == 1) {
                        $avaiable .= app('translator')->get('study.all_admins') . ', ';
                    }

                    if ($row->available_for_all_classes == 1) {
                        $avaiable .= app('translator')->get('study.all_classes_student') . ', ';
                    }


                    if (moduleStatusCheck('University')) {
                        if ($row->un_semester_label_id  && $row->un_section_id) {
                            $avaiable .= app('translator')->get('study.all_students_of') . ' ' . @$row->semesterLabel->name . '(' . @$row->unSection->section_name . '-' . @$row->undepartment->name . ')';
                        }
                    } else{
                        if ($row->classes && $row->sections) {
                            if(shiftEnable()){
                                $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . @$row->sections->section_name).', ' . @$row->shift->name;
                            }else{
                                $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . @$row->sections->section_name);
                            }
                        }

                        if ($row->classes && !$row->section) {
                            $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . app('translator')->get('study.all_sections')) . ', ';
                        }
                    }

                    return $avaiable;

                })
                ->addColumn('class_sections', function ($row) {
                    if (moduleStatusCheck('University')) {
                        $semLabel = $row->semesterLabel->name;
                        $academ = $row->unAcademic->name;

                        return $semLabel . '(' . $academ . ')';
                    }

                    if (($row->class) && ($row->section)) {
                        $classes = $row->classes->class_name;
                        $sections = $row->sections->section_name;
                        if(shiftEnable())
                        {
                            return $classes . '(' . $sections . ')' . '-' . $row->shift->name;
                        }else{
                            return $classes . '(' . $sections . ')' ;
                        }
                    }

                    if ($row->class && !$row->section) {
                        $classes = $row->classes->class_name;
                        $nullsections = app('translator')->get('common.all_sections');

                        return $classes . '(' . $nullsections . ')';
                    }

                    if ($row->section) {
                        return  $row->sections->section_name;
                    }

                    if ($row->class) {
                        return $row->classes->class_name;
                    }

                })
                ->rawColumns(['date'])
                ->addColumn('action', function ($row): string {
                    $btn = '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">'
                                    . app('translator')->get('common.select') .
                                '</button>
                                <div class="dropdown-menu dropdown-menu-right">';
                    if (userPermission('syllabus-list-view')) {
                        $btn .= '<a data-modal-size="modal-lg" title="' . __('study.view_content_details') . '" class="dropdown-item modalLink" href="' . route('upload-content-view', [$row->id]) . '">'
                                    . app('translator')->get('common.view') .
                                '</a>';
                    }

                    if (userPermission('syllabus-list-edit')) {
                        $btn .= '<a class="dropdown-item" href="' . route('upload-content-edit', [$row->id]) . '">'
                                    . app('translator')->get('common.edit') .
                                '</a>';
                    }

                    if (userPermission('syllabus-list-delete')) {
                        if (Config::get('app.app_sync')) {
                            $btn .= '<span data-toggle="tooltip" title="Disabled For Demo ">
                                        <a class="dropdown-item" href="#">' . app('translator')->get('common.disable') . '</a>
                                    </span>';
                        } else {
                            $btn .= '<a onclick="deleteAssignMent(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteApplyLeaveModal" data-id="' . $row->id . '">'
                                        . app('translator')->get('common.delete') .
                                    '</a>';
                        }
                    }

                    if (userPermission('syllabus-list-download') && $row->upload_file) {
                        $btn .= '<a class="dropdown-item" href="' . url($row->upload_file) . '" download>'
                                    . __('common.download') . ' <span class="pl ti-download"></span>
                                </a>';
                    }

                    $btn .= '</div></div>';

                    return $btn;
                })
                ->rawColumns(['action', 'date'])
                ->make(true);

    }

    public function complaintDetailsDatatable(Request $request)
    {
        if ($request->ajax()) {
            $complaints = SmComplaint::with('complaintType', 'complaintSource');

            return DataTables::of($complaints)
                ->addIndexColumn()
                ->addColumn('c_date', function ($row) {
                    return dateConvert(@$row->date);
                })
                ->addColumn('complaint_type', function ($row) {
                    return @$row->complaintType->name;
                })
                ->addColumn('complaint_source', function ($row) {
                    return @$row->complaintSource->name;
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('complaint_show') ? '<a class="dropdown-item modalLink" data-modal-size="large-modal" title="' . app('translator')->get('admin.complaint_details') . '" href="' . route('complaint_show', [$row->id]) . '">' . app('translator')->get('admin.complaint_details') . '</a>' : '') .
                        (userPermission('complaint_edit') ? '<a class="dropdown-item " href="' . route('complaint_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .
                        (userPermission('download-complaint-document') &&  $row->file ? '<a class="dropdown-item" href="' . url($row->file) . '" download>' . app('translator')->get('common.download') . '</a>' : '') .
                        (userPermission('complaint_delete') ? '<a class="dropdown-item" data-toggle="modal" onclick="deleteComplaint(' . $row->id . ');"  href="#">' . app('translator')->get('common.delete') . '</a>' : '') .

                        '</div>
                        </div>';
                })
                ->rawColumns(['action', 'complaint_type', 'complaint_source', 'c_date'])
                ->make(true);
        }

        return null;
    }

    public function unAssignStudentList(Request $request)
    {
        if ($request->ajax()) {
            $all_students = SmStudent::with(['parents' => function ($query) {
                return $query->select(['fathers_name', 'mothers_name', 'id', 'fathers_mobile']);
            }, 'gender' => function ($query) {
                return $query->select(['base_setup_name']);
            }, 'category' => function ($query) {
                return $query->select(['category_name', 'id']);
            }])
                ->wheredoesnthave('studentRecords')
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId());

            return DataTables::of($all_students)
                ->addIndexColumn()
                ->addColumn('dob', function ($row) {
                    return dateConvert(@$row->date_of_birth);
                })
                ->addColumn('full_name', function ($row): string {
                    return '<a target="_blank" href="' . route('student_view', [$row->id]) . '">' . $row->first_name . ' ' . $row->last_name . '</a>';
                })
                ->addColumn('mobile', function ($row): string {
                    return '<a href="tel:' . $row->mobile . '">' . $row->mobile . '</a>';
                })
                ->addColumn('action', function ($row): string {
                    $langName = (moduleStatusCheck('University')) ? app('translator')->get('university::un.assign_faculty_department') : app('translator')->get('student.assign_class');

                    return '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('student.assign-class') ? '<a class="dropdown-item" target="_blank" href="' . route('student.assign-class', [$row->id]) . '">' . $langName . '</a>' : '')

                        . ((userPermission('student.assign-class') && moduleStatusCheck('University')) ?
                            '<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id, 'assign_subject']) . '">' . app('translator')->get('university::un.assign_subject') . '</a>' : '')

                        . '<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                        (userPermission('student_edit') ? '<a class="dropdown-item" href="' . route('student_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                        (userPermission('disabled_student') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.disable') . '</a>') : '') .

                        '</div>
                                </div>';
                })
                ->rawColumns(['action', 'full_name', 'mobile', 'dob', 'full_name', 'mobile', 'dob'])
                ->make(true);
        }

        return null;
    }

    public function disableStudentList(Request $request)
    {

        if ($request->ajax()) {
            $user = auth()->user();
            $records = StudentRecord::query();
            $records->where('is_promote', 0)->where('school_id', $user->school_id);
            if (moduleStatusCheck('University')) {
                $records->when($request->filled('un_academic_id'), function ($u_query) use ($request): void {
                    $u_query->where('un_academic_id', $request->un_academic_id);
                })
                    ->when($request->filled('un_faculty_id'), function ($u_query) use ($request): void {
                        $u_query->where('un_faculty_id', $request->un_faculty_id);
                    })
                    ->when($request->filled('un_department_id'), function ($u_query) use ($request): void {
                        $u_query->where('un_department_id', $request->un_department_id);
                    })
                    ->when($request->filled('un_session_id'), function ($query) use ($request): void {
                        $query->where('un_session_id', $request->un_session_id);
                    })
                    ->when($request->filled('un_semester_label_id'), function ($query) use ($request): void {
                        $query->where('un_semester_label_id', $request->un_semester_label_id);
                    });

            } else {
                $records->when(!$request->academic_year, function ($query): void {
                    $query->where('academic_id', getAcademicId());
                }, function ($query) use ($request): void {
                    $query->where('academic_id', $request->academic_year);
                })->when($request->section, function ($query) use ($request): void {
                    $query->where('section_id', $request->section);
                })->when($request->class, function ($query) use ($request): void {
                    $query->where('class_id', $request->class);
                })->when($request->section_id, function ($query) use ($request): void {
                    $query->where('section_id', $request->section_id);
                })->when($request->class_id, function ($query) use ($request): void {
                    $query->where('class_id', $request->class_id);
                });

            }

            $roll_no = $request->roll_no;

            if (generalSetting()->multiple_roll && $roll_no) {
                $records->where('roll_no', 'like', '%' . $roll_no . '%');
            }


            $student_records = $records->whereHas('student')->get(['student_id'])->unique('student_id')->toArray();
            $all_students = SmStudent::with('studentRecords', 'studentRecords.class', 'studentRecords.section')->whereIn('id', $student_records)
                ->where('active_status', 0)
                ->with(['parents' => function ($query): void {
                    $query->select('id', 'fathers_name');
                }])
                ->with(['gender' => function ($query): void {
                    $query->select('id', 'base_setup_name');
                }])
                ->with(['category' => function ($query): void {
                    $query->select('id', 'category_name');
                }])
                ->when($request->name, function ($query) use ($request): void {
                    $query->where('full_name', 'like', '%' . $request->name . '%');
                })

            ->when($request->admission_no, function ($query) use ($request): void {
                $query->where('admission_no', 'like', '%' . $request->admission_no . '%');
            });

            if (!generalSetting()->multiple_roll && $roll_no) {
                $all_students->where('roll_no', 'like', '%' . $roll_no . '%');
            }
            if ($user->role_id == 4 && !userPermission('student.show-all-student')) {
                $classTeacher = SmClassTeacher::where('teacher_id', auth()->user()->staff->id)->pluck('assign_class_teacher_id');
                $assignClassTeacher = SmAssignClassTeacher::whereIn('id', $classTeacher)->get();

                $all_students = $all_students->whereHas('studentRecords', function ($q) use ($assignClassTeacher): void {
                    $q->whereIn('class_id', $assignClassTeacher->pluck('class_id'))
                        ->whereIn('section_id', $assignClassTeacher->pluck('section_id'));
                });
            }

            return DataTables::of($all_students)
                ->addIndexColumn()
                ->addColumn('dob', function ($row) {
                    return dateConvert(@$row->date_of_birth);
                })
                ->addColumn('full_name', function ($row): string {
                    return '<a target="_blank" href="' . route('student_view', [$row->id]) . '">' . $row->first_name . ' ' . $row->last_name . '</a>';
                })
                ->addColumn('mobile', function ($row): string {
                    return '<a href="tel:' . $row->mobile . '">' . $row->mobile . '</a>';
                })
                ->addColumn('semester_label', function ($row) {
                    $semester_label = [];
                    foreach ($row->studentRecords as $label) {
                        if (moduleStatusCheck('University')) {
                            $semester_label[] = $label->unSemesterLabel->name;
                        }
                    }

                    return $semester_label;
                })
                ->addColumn('class_sec', function ($row): string {
                    $class_sec = [];
                    foreach ($row->studentRecords as $classSec) {
                        if (moduleStatusCheck('University')) {
                            $class_sec[] = $classSec->unFaculty->name . '(' . $classSec->unDepartment->name . ')';
                        } else {
                            if(shiftEnable())
                            {
                                $class_sec[] = $classSec->class->class_name . '(' . $classSec->section->section_name . ')'. '[' . $classSec->shift->name . ']';
                            }else{
                                $class_sec[] = $classSec->class->class_name . '(' . $classSec->section->section_name . ')';
                            }
                        }
                    }

                    return implode(', ', $class_sec);
                })
                ->addColumn('action', function ($row): string {
                    $langName = (moduleStatusCheck('University')) ? app('translator')->get('university::un.assign_faculty_department') : app('translator')->get('student.assign_class');

                    return '<div class="dropdown CRM_dropdown">
                              <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                              <div class="dropdown-menu dropdown-menu-right">'

                        . (userPermission('disable_student_delete') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.delete') . '</a></span>' :
                            '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .

                        (userPermission('enable_student') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.delete') . '</a></span>' :
                            '<a onclick="enableId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#enableStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.enable') . '</a>') : '') .

                        '</div>
                          </div>';
                })
                ->rawColumns(['action', 'full_name', 'mobile', 'dob', 'class_sec', 'full_name', 'mobile', 'dob', 'class_sec'])
                ->make(true);
        }

        return null;
    }

    public function uploadContentListDatatable(Request $request)
    {

            $user = auth()->user();

            $uploadContents = SmTeacherUploadContent::query()
                ->where('school_id', $user->school_id)
                ->whereNullLms();

            // for teachers
            if (teacherAccess()) {
                $uploadContents = $uploadContents->where('created_by', $user->id);
            }

            // Handle sorting
            if ($request->has('order')) {
                $orderColumnIndex = $request->input('order.0.column');
                $orderDirection = $request->input('order.0.dir');
                $orderColumn = $request->input('columns.' . $orderColumnIndex . '.name');

                if (!empty($orderColumn)) {
                    $uploadContents = $uploadContents->orderBy($orderColumn, $orderDirection);
                }
            } else {
                $uploadContents = $uploadContents->latest();
            }

            if(moduleStatusCheck('University')){
                $uploadContents = $uploadContents->where('un_academic_id', getAcademicId())->with(['semesterLabel' => function ($query) {
                    return $query->select(['id', 'name']);
                }, 'unAcademic' => function ($query) {
                    return $query->select(['id', 'name']);
                }, 'unDepartment' => function ($query) {
                    return $query->select(['id', 'name']);
                }, 'unSection' => function ($query) {
                    return $query->select(['id', 'section_name']);
                }])->latest();
            } else{
                $uploadContents = $uploadContents->where('academic_id', getAcademicId())->with(['classes', 'sections'])->latest();
            }


                return DataTables::of($uploadContents)
                    ->addIndexColumn()
                    ->addColumn('upload_date', function ($row) {
                        return dateConvert($row->upload_date);
                    })
                    ->filterColumn('upload_date', function ($query, $keyword): void {
                        $date = date('Y-m-d', strtotime($keyword));

                        if ($date != '0') {
                            $query->whereDate('upload_date', '=', $date);
                        }
                    })
                    ->orderColumn('date', function ($query, $order): void {
                        $query->orderBy('upload_date', $order)->orderBy('upload_date', 'DESC');
                    })
                    ->addColumn('type', function ($row) {
                        $types = [
                            'as' => 'assignment',
                            'st' => 'study_material',
                            'sy' => 'syllabus',
                            'ot' => 'other',
                        ];

                        return __('study.' . ($types[$row->content_type] ?? 'other'));
                    })
                    ->addColumn('avaiable', function ($row): string {
                        $avaiable = '';
                        if ($row->available_for_admin == 1) {
                            $avaiable .= app('translator')->get('study.all_admins') . ', ';
                        }

                        if ($row->available_for_all_classes == 1) {
                            $avaiable .= app('translator')->get('study.all_classes_student') . ', ';
                        }
                        
                        


                        if (moduleStatusCheck('University')) {
                            if ($row->un_semester_label_id  && $row->un_section_id) {
                                $avaiable .= app('translator')->get('study.all_students_of') . ' ' . @$row->semesterLabel->name . '(' . @$row->unSection->section_name . '-' . @$row->undepartment->name . ')';
                            }
                        } else{
                            if ($row->classes && $row->sections) {
                                $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . @$row->sections->section_name) . ', ';
                            }

                            if ($row->classes && !$row->section) {
                                $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . app('translator')->get('study.all_sections')) . ', ';
                            }
                        }

                        return $avaiable;

                    })
                    ->addColumn('class_sections', function ($row) {
                        
                        if($row->available_for_all_classes)
                        {
                            return "All Classes";
                        }
                        
                        if (moduleStatusCheck('University')) {
                            $semLabel = $row->semesterLabel->name;
                            $academ = $row->unAcademic->name;

                            return $semLabel . '(' . $academ . ')';
                        }

                        if (($row->class) && ($row->section) && ($row->class) && ($row->shift_id)) {
                            $classes = $row->classes->class_name;
                            $sections = $row->sections->section_name;
                            $shift = $row->shift->name;
                            if(shiftEnable())
                            {
                                return $classes . '(' . $sections . ')' . '[' . $shift . ']';   
                            }else{
                                return $classes . '(' . $sections . ')';
                            }
                        }

                        if (($row->class) && ($row->section)) {
                            $classes = $row->classes->class_name;
                            $sections = $row->sections->section_name;
                            return $classes . '(' . $sections . ')';
                        }

                        if ($row->class && !$row->section) {
                            $classes = $row->classes->class_name;
                            $nullsections = app('translator')->get('common.all_sections');

                            return $classes . '(' . $nullsections . ')';
                        }

                        if ($row->section) {
                            return  $row->sections->section_name;
                        }

                        if ($row->class) {
                            return $row->classes->class_name;
                        }

                    })
                    ->addColumn('action', function ($row): string {
                        $btn = '<div class="dropdown CRM_dropdown">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">'
                            . __('common.select') .
                            '</button>
                            <div class="dropdown-menu dropdown-menu-right">';

                        if (userPermission('upload-content-view')) {
                            $btn .= '<a data-modal-size="modal-lg" title="' . __('study.view_content_details') . '" class="dropdown-item modalLink" href="' . route('upload-content-view', [$row->id]) . '">' . __('common.view') . '</a>';
                        }

                        if (userPermission('upload-content-edit')) {
                            $btn .= '<a class="dropdown-item" href="' . route('upload-content-edit', [$row->id]) . '">' . __('common.edit') . '</a>';
                        }

                        if (userPermission('delete-upload-content')) {
                            $btn .= '<a onclick="deleteUpContent(' . $row->id . ');" class="dropdown-item">' . __('common.delete') . '</a>';
                        }

                        if (userPermission('download-content-document') && $row->upload_file) {
                            $btn .= '<a class="dropdown-item" href="' . url($row->upload_file) . '" download>' . __('common.download') . ' <span class="pl ti-download"></span></a>';
                        }

                        $btn .= '</div></div>';

                        return $btn;
                    })

                    ->rawColumns(['action', 'upload_date'])
                    ->make(true);

    }

    public function otherDownloadList()
    {

        $user = Auth()->user();

        if (teacherAccess()) {
            SmNotification::where('user_id', $user->id)->where('role_id', 1)->update(['is_read' => 1]);
        }

        if (!teacherAccess()) {
            $uploadContents = SmTeacherUploadContent::where('content_type', 'ot')
                ->where('school_id', $user->school_id)
                ->whereNullLms();
        } else {
            $uploadContents = SmTeacherUploadContent::where(function ($q) use ($user): void {
                $q->where('created_by', $user->id)->orWhere('available_for_admin', 1);
            })->where('content_type', 'ot')->whereNullLms()
                ->where('school_id', $user->school_id);
        }

        if(moduleStatusCheck('University')){
            $uploadContents = $uploadContents->where('un_academic_id', getAcademicId())->with(['semesterLabel' => function ($query) {
                return $query->select(['id', 'name']);
            }, 'unAcademic' => function ($query) {
                return $query->select(['id', 'name']);
            }, 'unDepartment' => function ($query) {
                return $query->select(['id', 'name']);
            }, 'unSection' => function ($query) {
                return $query->select(['id', 'section_name']);
            }])->select([
                'id',
                'content_title',
                'upload_date',
                'content_type',
                'upload_file',
                'description',
                'available_for_admin',
                'available_for_all_classes',
                'un_academic_id',
                'un_semester_label_id',
                'un_department_id',
                'un_section_id',
            ]);
        } else{
            $uploadContents = $uploadContents->where('academic_id', getAcademicId())->with(['classes' => function ($query) {
                return $query->select(['id', 'class_name']);
            }, 'sections' => function ($query) {
                return $query->select(['id', 'section_name']);
            }])->select([
                'id',
                'content_title',
                'upload_date',
                'content_type',
                'upload_file',
                'description',
                'available_for_admin',
                'available_for_all_classes',
                'class',
                'section',
                'shift_id'
            ]);
        }

       
        return DataTables::of($uploadContents)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return dateConvert(@$row->upload_date);
            })
            ->addColumn('type', function ($row) {
                
                if ($row->content_type == 'ot') {
                    $type = 'other';
                }

                return __('study.' . $type);

            })
            ->addColumn('avaiable', function ($row): string {
                
                $avaiable = '';
                if ($row->available_for_admin == 1) {
                    $avaiable .= app('translator')->get('study.all_admins') . ', ';
                }

                if ($row->available_for_all_classes == 1) {
                    $avaiable .= app('translator')->get('study.all_classes_student') . ', ';
                }


                if (moduleStatusCheck('University')) {
                    if ($row->un_semester_label_id  && $row->un_section_id) {
                        $avaiable .= app('translator')->get('study.all_students_of') . ' ' . @$row->semesterLabel?->name . '(' . @$row->unSection?->section_name . '-' . @$row->undepartment?->name . ')';
                    }
                } else{
                    if ($row->classes && $row->sections) {
                        if(shiftEnable()){
                            $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . @$row->sections->section_name . '->' . @$row->shift?->name);
                        }else{
                            $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . @$row->sections->section_name );
                        }
                    }

                    if ($row->classes && !$row->section) {
                        $avaiable .= (app('translator')->get('study.all_students_of') . ' ' . $row->classes->class_name . '->' . app('translator')->get('study.all_sections')) . ', ';
                    }
                }
                

                return $avaiable;

            })
            ->addColumn('class_sections', function ($row) {
                
                if (moduleStatusCheck('University')) {
                    $semLabel = $row->semesterLabel->name;
                    $academ = $row->unAcademic->name;

                    return $semLabel . '(' . $academ . ')';
                }

                if (($row->class) && ($row->section)) {
                   
                    $classes = $row->classes->class_name;
                    $sections = $row->sections->section_name;
                    if(shiftEnable())
                    {
                        return $classes . '(' . $sections . ')' . '-' . $row->shift->name;
                    }else{
                        return $classes . '(' . $sections . ')' ;
                    }
                }
                    
                if ($row->class && !$row->section) {
                    $classes = $row->classes->class_name;
                    $nullsections = app('translator')->get('common.all_sections');

                    return $classes . '(' . $nullsections . ')';
                }

                if ($row->section) {
                    return  $row->sections->section_name;
                }

                if ($row->class) {
                    return $row->classes->class_name;
                }

            })
            ->rawColumns(['date'])
            ->addColumn('action', function ($row): string {
                $btn = '<div class="dropdown CRM_dropdown">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">'
                                . app('translator')->get('common.select') .
                            '</button>
                            <div class="dropdown-menu dropdown-menu-right">';
                if (userPermission('other-download-list-view')) {
                    $btn .= '<a data-modal-size="modal-lg" title="' . __('study.view_content_details') . '" class="dropdown-item modalLink" href="' . route('upload-content-view', [$row->id]) . '">'
                                . app('translator')->get('common.view') .
                            '</a>';
                }

                if (userPermission('other-download-list-edit')) {
                    $btn .= '<a class="dropdown-item" href="' . route('upload-content-edit', [$row->id]) . '">'
                                . app('translator')->get('common.edit') .
                            '</a>';
                }

                if (userPermission('other-download-list-delete')) {
                    if (Config::get('app.app_sync')) {
                        $btn .= '<span data-toggle="tooltip" title="Disabled For Demo ">
                                    <a class="dropdown-item" href="#">' . app('translator')->get('common.disable') . '</a>
                                </span>';
                    } else {
                        $btn .= '<a onclick="deleteOtherDownload(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteApplyLeaveModal" data-id="' . $row->id . '">'
                                    . app('translator')->get('common.delete') .
                                '</a>';
                    }
                }

                if (userPermission('other-download-list-download') && $row->upload_file) {
                    $btn .= '<a class="dropdown-item" href="' . url($row->upload_file) . '" download>'
                                . __('common.download') . ' <span class="pl ti-download"></span>
                            </a>';
                }

                $btn .= '</div></div>';

                return $btn;
            })
            ->rawColumns(['action', 'date'])
            ->make(true);
    }

    public function ajaxFeesPayment(Request $request)
    {
        if ($request->ajax()) {
            $date_from = date('Y-m-d', strtotime($request->date_from));
            $date_to = date('Y-m-d', strtotime($request->date_to));
            $fees_payments = SmFeesPayment::query();
            $fees_payments = $fees_payments->when(directFees(), function ($q): void {
                $q->whereNotNull('installment_payment_id');
            });

            if (moduleStatusCheck('University')) {
                $fees_payments->when($request->un_semester_label_id, function ($q) use ($request): void {
                    $q->whereHas('studentInfo', function ($q) use ($request) {
                        return $q->where(function ($q) use ($request) {
                            return $q->where('un_semester_label_id', $request->un_semester_label_id);
                        });
                    });
                });

            } else {
                if ($request->class) {
                    $fees_payments->whereHas('recordDetail', function ($q) use ($request) {
                        return $q->where('class_id', $request->class);
                    });
                }

                if ($request->section) {
                    $fees_payments->whereHas('recordDetail', function ($q) use ($request) {
                        return $q->where('section_id', $request->section);
                    });
                }
            }

            $fees_payments->when($request->keyword, function ($q) use ($request): void {
                $q->whereHas('studentInfo', function ($q) use ($request) {
                    return $q->where(function ($q) use ($request) {
                        return $q->where('full_name', 'like', '%' . @$request->keyword . '%')
                            ->orWhere('admission_no', 'like', '%' . @$request->keyword . '%')
                            ->orWhere('roll_no', 'like', '%' . @$request->keyword . '%');
                    });
                });
            });
            $fees_payments->when($request->date_from && $request->date_to == null, function ($query) use ($date_from): void {
                $query->whereDate('payment_date', '=', $date_from);
            });
            $fees_payments->when($request->date_to && $request->date_from == null, function ($query) use ($date_to): void {
                $query->whereDate('payment_date', '=', $date_to);
            });
            $fees_payments->when($request->date_from && $request->date_to, function ($query) use ($date_from, $date_to): void {
                $query->whereDate('payment_date', '>=', $date_from)->whereDate('payment_date', '<=', $date_to);
            })->where('active_status', 1)->orderby('id', 'DESC')->where('school_id', Auth::user()->school_id);
            if (auth()->user()->role_id != 1 && auth()->user()->role_id != 5) {
                $fees_payments = $fees_payments->where('created_by', auth()->user()->id);
            }

            $fees_payments = $fees_payments->whereHas('recordDetail')->with('feesType', 'feesInstallment.installment', 'feesInstallment', 'recordDetail', 'installmentPayment', 'recordDetail.studentDetail', 'recordDetail.class', 'recordDetail.section', 'studentInfo');

            return DataTables::of($fees_payments)
                ->addIndexColumn()
                ->addColumn('invoice', function ($row) {
                    if (moduleStatusCheck('University')) {
                        universityFeesInvoice(@$row->installmentPayment->invoice_no);
                    } elseif (directFees()) {
                        $invoice_setting = FeesInvoice::where('school_id', auth()->user()->school_id)->first(['prefix', 'start_form']);

                        return sm_fees_invoice($row->installmentPayment->invoice_no, $invoice_setting);
                    } else {
                        return $row->id . '/' . $row->fees_type_id;
                    }

                })
                ->addColumn('date', function ($row) {
                    return dateConvert(@$row->payment_date);
                })
                ->addColumn('fees_amount', function ($row): string {
                    return generalSetting()->currency_symbol . ' ' . $row->amount;
                })
                ->addColumn('class_sec', function ($row): string {
                    if (moduleStatusCheck('University')) {
                        return $row->recordDetail->semesterLabel->name . '(' . $row->recordDetail->unDepartment->name . ')';
                    }

                    return $row->recordDetail->class->class_name . '(' . $row->recordDetail->section->section_name . ')';

                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a data-modal-size="modal-lg" target="_blank" class="dropdown-item" href="' . route('fees_collect_student_wise', [$row->recordDetail->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                        ((userPermission('edit-fees-payment') && $row->assign_id != null) ? '<a class="dropdown-item modalLink" data-modal-size="modal-lg" title="' . __('fees.edit_fees_payment') . '" href="' . route('edit-fees-payment', [$row->id]) . '">' . app('translator')->get('fees.edit_fees') . '</a>' : '') .

                        (!moduleStatusCheck('University') && directFees() == false ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteFeesPayment(' . $row->id . ');"  class="dropdown-item" href="#" data-toggle="modal" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .

                        '</div>
                                </div>';
                })
                ->rawColumns(['action', 'date', 'invoice', 'fees_amount'])
                ->make(true);
        }

        return null;
    }

    // public function ajaxIncomeList(Request $request)
    // {

    //     if ($request->ajax()) {
    //         $all_incomes = SmAddIncome::with(['paymentMethod:method,id', 'ACHead:head,id'])
    //             ->where('academic_id', getAcademicId())
    //             ->orderBy('date', 'DESC')
    //             ->select(['id', 'amount', 'date', 'payment_method_id', 'name', 'income_head_id', 'created_by']);

    //         return DataTables::of($all_incomes)
    //             ->addIndexColumn()
    //             ->addColumn('date', function ($row) {
    //                 return dateConvert(@$row->date);
    //             })
    //             ->addColumn('amount', function ($row): string {
    //                 return generalSetting()->currency_symbol . ' ' . $row->amount;
    //             })
    //             ->addColumn('action', function ($row): string {

    //                 return '<div class="dropdown CRM_dropdown">
    //                             <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
    //                             <div class="dropdown-menu dropdown-menu-right">'
    //                     . (userPermission('add_income_edit') ? '<a class="dropdown-item" href="' . route('add_income_edit', [$row->id]) . '">' . __('common.edit') . '</a>' : '')

    //                     . ((userPermission('add_income_delete')) ?
    //                         '<a onclick="deleteIncome(' . $row->id . ');"  class="dropdown-item"  href="#">' . app('translator')->get('common.delete') . '</a>' : '') .

    //                     (($row->file != '') ?
    //                         '<a   class="dropdown-item"  download href="' . url($row->file) . '">' . app('translator')->get('common.download') . ' <span class="pl ti-download"></span> </a>' : '') .
    //                     '</div>
    //                         </div>';
    //             })
    //             ->rawColumns(['action', 'date'])
    //             ->make(true);
    //     }

    //     return null;

    // }
    public function ajaxIncomeList(Request $request)
    {
        if ($request->ajax()) {
            $all_incomes = SmAddIncome::with(['paymentMethod:id,method', 'ACHead:id,head'])
                ->where('academic_id', getAcademicId())
                ->orderBy('date', 'DESC')
                ->select(['id', 'amount', 'date', 'payment_method_id', 'name', 'income_head_id', 'created_by']);

            return DataTables::eloquent($all_incomes)
                ->addIndexColumn()
                ->addColumn('date', fn($row) => dateConvert(@$row->date))
                ->addColumn('amount', fn($row) => generalSetting()->currency_symbol . ' ' . $row->amount)
                ->addColumn('payment_method.method', fn($row) => optional($row->paymentMethod)->method)
                ->addColumn('a_c_head.head', fn($row) => optional($row->ACHead)->head)
                ->addColumn('action', function ($row) {
                    return '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . __('common.select') . '</button>
                                <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('add_income_edit') ? '<a class="dropdown-item" href="' . route('add_income_edit', [$row->id]) . '">' . __('common.edit') . '</a>' : '')
                        . (userPermission('add_income_delete') ? '<a onclick="deleteIncome(' . $row->id . ');"  class="dropdown-item"  href="#">' . __('common.delete') . '</a>' : '')
                        . ($row->file ? '<a class="dropdown-item" download href="' . url($row->file) . '">' . __('common.download') . '</a>' : '')
                        . '</div></div>';
                })
                ->rawColumns(['action', 'date'])
                ->make(true);
        }

        return null;
    }

    public function ajaxExpenseList(Request $request)
    {
        if ($request->ajax()) {
            $all_expenses = SmAddExpense::with('expenseHead', 'ACHead', 'paymentMethod', 'account')
                ->orderBy('date', 'DESC')
                ->select(['id', 'name', 'date', 'amount', 'payment_method_id', 'expense_head_id']);

            return DataTables::eloquent($all_expenses)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return dateConvert(@$row->date);
                })
                ->addColumn('amount', function ($row): string {
                    return generalSetting()->currency_symbol . ' ' . $row->amount;
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . __('common.select') . '</button>
                                <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('add-expense-edit') ? '<a class="dropdown-item" href="' . route('add-expense-edit', [$row->id]) . '">' . __('common.edit') . '</a>' : '')
                        . (userPermission('add-expense-delete') ? '<a onclick="deleteExpense(' . $row->id . ');"  class="dropdown-item" href="#">' . __('common.delete') . '</a>' : '')
                        . (!empty($row->file) ? '<a class="dropdown-item" download href="' . url($row->file) . '">' . __('common.download') . ' <span class="pl ti-download"></span> </a>' : '')
                        . '</div></div>';
                })
                ->rawColumns(['status', 'date', 'amount', 'action'])
                ->make(true);
        }

        return null;
    }


    public function ajaxPendingLeave(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            if (checkAdmin() == true) {
                $apply_leaves = SmLeaveRequest::with([
                    'leaveType' => function ($query) {
                        return $query->select(['id', 'type']);
                    },
                    'leaveDefine' => function ($query) {
                        return $query->select(['id', 'type_id']);
                    },
                    'user:id,full_name',
                ])
                    ->where([['active_status', 1], ['approve_status', '!=', 'A']])
                    ->where('school_id', $user->school_id)
                    ->where('academic_id', getAcademicId())
                    ->select(['leave_from', 'id', 'type_id', 'leave_to', 'apply_date', 'approve_status','staff_id']);
            } elseif ($user->role_id == 4) {
                $staff = $user->staff;
                $class_teacher = SmClassTeacher::where('teacher_id', $staff->id)
                    ->where('school_id', $user->school_id)
                    ->where('academic_id', getAcademicId())
                    ->first();

                if ($class_teacher) {
                    $leaves = SmLeaveRequest::where([
                        ['active_status', 1],
                        ['approve_status', '!=', 'A'],
                        ['role_id', '=', 2],
                    ])
                        ->where('school_id', $user->school_id)
                        ->where('academic_id', getAcademicId())
                        ->first();
                    $smAssignClassTeacher = SmAssignClassTeacher::find($class_teacher->assign_class_teacher_id);
                    if ($leaves) {
                        $apply_leaves = SmLeaveRequest::with([
                            'leaveType' => function ($query) {
                                return $query->select(['id', 'type']);
                            },
                            'leaveDefine' => function ($query) {
                                return $query->select(['id', 'type_id']);
                            },
                            'user:id,full_name',
                        ])
                            ->with(['student' => function ($query) use ($smAssignClassTeacher): void {
                                $query->where('class_id', $smAssignClassTeacher->class_id)->where('section_id', $smAssignClassTeacher->section_id);
                            }])->where([
                                ['active_status', 1],
                                ['approve_status', '!=', 'A'],
                                ['role_id', '=', 2],
                            ])->where('school_id', $user->school_id)
                            ->where('academic_id', getAcademicId())
                            ->select(['leave_from', 'id', 'type_id', 'leave_to', 'apply_date', 'approve_status','staff_id']);

                    }
                } else {
                    $apply_leaves = SmLeaveRequest::with([

                        'leaveDefine' => function ($query) {
                            return $query->select(['id', 'type_id']);
                        },
                    ])->where([
                        ['active_status', 1],
                        ['approve_status', '!=', 'A'],
                        ['staff_id', '=', auth()->user()->staff->id],
                        ['role_id', '!=', 2],
                    ])
                        ->where('school_id', Auth::user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->select(['leave_from', 'id', 'type_id', 'leave_to', 'apply_date', 'approve_status','staff_id']);
                }
            } elseif (auth()->user()->role_id == 1) {
                $apply_leaves = SmLeaveRequest::with('leaveDefine', 'leaveType', 'user:id,full_name', 'leaveType')->where([['active_status', 1], ['approve_status', '!=', 'A']])
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId());
            } else {
                $apply_leaves = SmLeaveRequest::with('leaveType', 'leaveDefine', 'user:id,full_name')->where([['active_status', 1], ['approve_status', '!=', 'A']])
                    ->where('school_id', Auth::user()->school_id)
                    ->where([
                        ['active_status', 1],
                        ['approve_status', '!=', 'A'],
                        ['staff_id', '=', auth()->user()->staff->id],
                        ['role_id', '!=', 2],
                    ])
                    ->where('academic_id', getAcademicId());
            }

            return $data = DataTables::of($apply_leaves)
                ->addIndexColumn()
                ->addColumn('f_date', function ($row) {
                    return dateConvert(@$row->leave_from);
                })
                ->addColumn('t_date', function ($row) {
                    return dateConvert(@$row->leave_to);
                })
                ->addColumn('a_date', function ($row) {
                    return dateConvert(@$row->apply_date);
                })
                ->addColumn('status', function ($row): string {
                    if ($row->approve_status == 'P') {
                        $btn = '<button class="primary-btn bg-warning text-white border-0 small tr-bg">' . app('translator')->get('common.pending') . '</button>';
                    } elseif ($row->approve_status == 'A') {
                        $btn = '<button class="primary-btn bg-success text-white border-0 small tr-bg">' . app('translator')->get('common.approved') . '</button>';
                    } elseif ($row->approve_status == 'C') {
                        $btn = '<button class="primary-btn small bg-danger text-white border-0">' . app('translator')->get('common.cancelled') . '</button>';
                    }

                    return $btn;

                })
                ->addColumn('action', function ($row): string {

                    return '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                    <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('approve-leave-edit') ? '<a class="dropdown-item modalLink" data-modal-size="modal-lg" title="View/Edit Leave Details" href="' . route('view-leave-details-approve', [@$row->id]) . '">' . __('common.view') . '</a>' : '')

                        . ((userPermission('approve-leave-delete')) ?
                            '<a onclick="deleteApplyLeave(' . $row->id . ');"  class="dropdown-item"  href="#">' . app('translator')->get('common.delete') . '</a>' : '') .

                        '</div>
                                </div>';
                })
                ->rawColumns(['status', 'action', 'f_date', 't_date', 'a_date'])
                ->make(true);

        }

        return null;

    }

    public function ajaxApproveLeave(Request $request)
    {

        if ($request->ajax()) {
            $user = Auth::user();
            if ($user->role_id == 1) {
                $apply_leaves = SmLeaveRequest::with([
                    'leaveType' => function ($query) {
                        return $query->select(['id', 'type']);
                    },
                    'leaveDefine' => function ($query) {
                        return $query->select(['id', 'type_id']);
                    },
                    'user:id,full_name',
                ])
                    ->where([['active_status', 1], ['approve_status', '!=', 'P']])
                    ->where('school_id', $user->school_id)
                    ->where('academic_id', getAcademicId())
                    ->select(['leave_from', 'id', 'type_id', 'leave_to', 'apply_date', 'approve_status', 'staff_id']);
            } elseif ($user->staff) {
                $apply_leaves = SmLeaveRequest::with([
                    'leaveType' => function ($query) {
                        return $query->select(['id', 'type']);
                    },
                    'leaveDefine' => function ($query) {
                        return $query->select(['id', 'type_id']);
                    },
                    'user:id,full_name',
                ])
                    ->where([['active_status', 1], ['approve_status', '!=', 'P'], ['staff_id', '=', auth()->user()->id]])
                    ->where('academic_id', getAcademicId())
                    ->select(['leave_from', 'id', 'type_id', 'leave_to', 'apply_date', 'approve_status', 'staff_id']);
            }

            return DataTables::of($apply_leaves)
                ->addIndexColumn()
                ->addColumn('f_date', function ($row) {
                    return dateConvert(@$row->leave_from);
                })
                ->addColumn('t_date', function ($row) {
                    return dateConvert(@$row->leave_to);
                })
                ->addColumn('a_date', function ($row) {
                    return dateConvert(@$row->apply_date);
                })
                ->addColumn('status', function ($row): string {
                    $btn = '';
                    if ($row->approve_status == 'P') {
                        $btn = '<button class="primary-btn bg-warning text-white border-0 small tr-bg">' . app('translator')->get('common.pending') . '</button>';
                    } elseif ($row->approve_status == 'A') {
                        $btn = '<button class="primary-btn bg-success text-white border-0 small tr-bg">' . app('translator')->get('common.approved') . '</button>';
                    } elseif ($row->approve_status == 'C') {
                        $btn = '<button class="primary-btn small bg-danger text-white border-0">' . app('translator')->get('common.cancelled') . '</button>';
                    }

                    return $btn;

                })
                ->addColumn('action', function ($row): string {

                    return '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                    <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('approve-leave-edit') ? '<a class="dropdown-item modalLink" data-modal-size="modal-lg" title="View/Edit Leave Details" href="' . route('view-leave-details-approve', [@$row->id]) . '">' . __('common.view') . '</a>' : '')

                        . ((userPermission('approve-leave-delete')) ?
                            '<a onclick="deleteApplyLeave(' . $row->id . ');"  class="dropdown-item"  href="#">' . app('translator')->get('common.delete') . '</a>' : '') .

                        '</div>
                                </div>';
                })
                ->rawColumns(['status', 'action', 'f_date', 't_date', 'a_date'])
                ->make(true);
        }

        return null;

    }

    public function homeworkListAjax(Request $request)
    {

        if ($request->ajax()) {
            $all_homeworks = SmHomework::query();
            $all_homeworks->with('classes', 'sections', 'subjects', 'users', 'shift');
            $all_homeworks->when($request->class, function ($query) use ($request): void {
                $query->where('class_id', $request->class);
            });
            $all_homeworks->when($request->subject, function ($query) use ($request): void {
                $query->where('subject_id', $request->subject);
            });
            $all_homeworks->when($request->section, function ($query) use ($request): void {
                $query->where('section_id', $request->section);
            });
            $all_homeworks->when($request->shift, function ($query) use ($request): void {
                $query->where('shift_id', $request->shift);
            });
            $all_homeworks->whereNull('course_id');

            if (moduleStatusCheck('University')) {
                $all_homeworks->with('semesterLabel', 'unSession', 'unSemester');
            }

            $all_homeworks->where('school_id', Auth::user()->school_id)->orderby('id', 'DESC')
                ->where('academic_id', getAcademicId());
            if (teacherAccess()) {
                $homeworkLists = $all_homeworks->where('created_by', Auth::user()->id);
            } else {
                $homeworkLists = $all_homeworks;
            }

            return DataTables::of($homeworkLists)
                ->addIndexColumn()
                ->addColumn('homework_date', function ($row) {
                    return dateConvert(@$row->homework_date);
                })
                ->addColumn('submission_date', function ($row) {
                    return dateConvert(@$row->submission_date);
                })
                ->addColumn('evaluation_date', function ($row) {
                    return $row->evaluation_date ? dateConvert(@$row->evaluation_date) : '-';
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                            <div class="dropdown-menu dropdown-menu-right">'

                        . ((userPermission('university.unevaluation-homework') && moduleStatusCheck('University')) ? '<a class="dropdown-item"  href="' . route('university.unevaluation-homework', [$row->un_semester_label_id, $row->id]) . '">' . app('translator')->get('homework.evaluation') . '</a>' : '') .
                        ((userPermission('homework_edit')) ? '<a class="dropdown-item"  href="' . route('homework_edit', [$row->id]) . '">' . __('common.edit') . '</a>' : '') .

                        ((userPermission('evaluation-homework') && moduleStatusCheck('University') == false) ? '<a class="dropdown-item"  href="' . route('evaluation-homework', [@$row->class_id, @$row->section_id, @$row->id]) . '">' . __('homework.evaluation') . '</a>' : '') .

                        (userPermission('homework_delete') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteHomeWork(' . $row->id . ');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteApplyLeaveModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .
                        '</div>
                                </div>';
                })
                ->rawColumns(['action', 'homework_date', 'submission_date', 'evaluation_date'])
                ->make(true);
        }

        return null;
    }

    public function bookListAjax(Request $request)
    {
        if ($request->ajax()) {
            $books = SmBook::leftJoin('sm_subjects', 'sm_books.book_subject_id', '=', 'sm_subjects.id')
                ->leftJoin('sm_book_categories', 'sm_books.book_category_id', '=', 'sm_book_categories.id')
                ->select(
                    'sm_books.*',
                    'sm_subjects.subject_name',
                    'sm_book_categories.category_name'
                )
                ->orderBy('sm_books.id', 'DESC');

            return DataTables::of($books)
                ->filter(function ($query) use ($request) {
                    // for search query
                    if ($request->has('search') && $request->input('search.value') != '') {
                        $search = $request->input('search.value');
                        $query->where(function ($q) use ($search) {
                            $q->where('sm_books.book_title', 'like', "%{$search}%")
                            ->orWhere('sm_books.book_number', 'like', "%{$search}%")
                            ->orWhere('sm_books.isbn_no', 'like', "%{$search}%")
                            ->orWhere('sm_books.author_name', 'like', "%{$search}%")
                            ->orWhere('sm_books.publisher_name', 'like', "%{$search}%")
                            ->orWhere('sm_book_categories.category_name', 'like', "%{$search}%")
                            ->orWhere('sm_subjects.subject_name', 'like', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()
                ->editColumn('book_title',function($books){
                    return $books->book_title;
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                <div class="dropdown-menu dropdown-menu-right">'
                            . (userPermission('edit-book') ? '<a class="dropdown-item" href="' . route('edit-book', [$row->id]) . '">' . __('common.edit') . '</a>' : '')
                            . (userPermission('delete-book-view') ? '<a class="dropdown-item deleteUrl" data-modal-size="modal-md" title="' . __('library.delete_book') . '" href="' . route('delete-book-view', [$row->id]) . '">' . __('common.delete') . '</a>' : '')
                            . '</div>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return null;
    }

    public function allIssuedBookAjax(Request $request)
    {
        if ($request->ajax()) {
            $book_id = $request->book_id;
            $book_number = $request->book_number;
            $subject_id = $request->subject_id;

            $all_issue_books = SmBookIssue::query()
                ->with([
                    'user:id,full_name',
                    'books:id,book_subject_id,book_title,book_number,isbn_no,author_name',
                    'books.bookSubject'
                ])
                ->whereHas('books', function ($query) use ($book_id, $book_number, $subject_id) {
                    if ($book_id) {
                        $query->where('id', $book_id);
                    }
                    if ($book_number) {
                        $query->where('book_number', $book_number);
                    }
                    if ($subject_id) {
                        $query->where('book_subject_id', $subject_id);
                    }
                });

            return DataTables::of($all_issue_books)
                ->addIndexColumn()
                ->addColumn('issue_status', function ($row): string {
                    $now = new DateTimeImmutable();
                    $dueDate = new DateTimeImmutable($row->due_date);

                    if ($row->issue_status === 'I') {
                        if ($dueDate < $now) {
                            return '<button class="primary-btn small bg-danger text-white border-0">' . __('library.expired') . '</button>';
                        } else {
                            return '<button class="primary-btn small bg-success text-white border-0">' . __('library.issued') . '</button>';
                        }
                    } else {
                        return '<button class="primary-btn small bg-success text-white border-0">' . __('library.returned') . '</button>';
                    }
                })
                ->addColumn('given_date', function ($row) {
                    return dateConvert($row->given_date);
                })
                ->addColumn('due_date', function ($row) {
                    return dateConvert($row->due_date);
                })
                ->rawColumns(['issue_status', 'given_date', 'due_date'])
                ->make(true);
        }

        return null;
    }

    public function itemsListAjax(Request $request)
    {

        if ($request->ajax()) {
            $items = SmItem::with('category:id,category_name')
                ->where('school_id', Auth::user()->school_id)
                ->select(['id', 'item_name', 'total_in_stock', 'description','item_category_id']);

            return DataTables::of($items)
                ->addIndexColumn()
                ->addColumn('action', function ($row): string {

                    return '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                    <div class="dropdown-menu dropdown-menu-right">'
                        . (userPermission('item-list-edit') ? '<a class="dropdown-item" href="' . route('item-list-edit', $row->id) . '">' . __('common.edit') . '</a>' : '')

                        . ((userPermission('delete-item-view')) ?
                            '<a  class="dropdown-item deleteUrl" data-modal-size="modal-md" title="' . __('inventory.delete_item') . '"  href="' . route('delete-item-view', @$row->id) . '">' . app('translator')->get('common.delete') . '</a>' : '') .

                        '</div>
                                </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return null;
    }

    public function itemReceiveListAjax(Request $request)
    {
        if ($request->ajax()) {
            $allItemReceiveLists = SmItemReceive::with('suppliers', 'paymentMethodName', 'bankName', 'itemPayments');

            return DataTables::of($allItemReceiveLists)
                ->addIndexColumn()
                ->addColumn('receive_date', function ($row) {
                    return dateConvert($row->receive_date);
                })
                ->addColumn('grand_total', function ($row): string {
                    return number_format((float)$row->grand_total, 2, '.', '');
                })
                ->addColumn('total_paid', function ($row): string {
                    return number_format((float)$row->total_paid, 2, '.', '');
                })
                ->addColumn('total_due', function ($row): string {
                    return number_format((float)$row->total_due, 2, '.', '');
                })
                ->addColumn('status', function ($row): string {
                    if ($row->paid_status == 'P') {
                        $btn = '<button class="primary-btn small bg-success text-white border-0">' . __('inventory.paid') . '</button>';
                    } elseif ($row->paid_status == 'PP') {
                        $btn = '<button class="primary-btn small bg-warning text-white border-0">' . __('inventory.partial') . '</button>';
                    } elseif ($row->paid_status == 'U') {
                        $btn = '<button class="primary-btn small bg-danger text-white border-0">' . __('inventory.unpaid') . '</button>';
                    } else {
                        $btn = '<button class="primary-btn small bg-info text-white border-0">' . __('inventory.refund') . '</button>';
                    }

                    return $btn;

                })
                ->addColumn('action', function ($row): string {
                    $btn = '';
                    $btn .= '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>
                                    <div class="dropdown-menu dropdown-menu-right">';
                    if ($row->paid_status != 'R') {
                        if (!$row->item_payments) {
                            $btn .= (userPermission('edit-item-receive') ? '<a class="dropdown-item" href="' . route('edit-item-receive', $row->id) . '">' . __('common.edit') . '</a>' : '');
                        }

                        if ($row->total_due > 0) {
                            $btn .= '<a class="dropdown-item modalLink" title="Add Payment" data-modal-size="modal-md" href="' . route('add-payment', $row->id) . '">' . __('common.add_payment') . '</a>';
                        }
                    }

                    if ($row->paid_status != 'P') {
                        $btn .= (userPermission('view-receive-payments') ? '<a class="dropdown-item modalLink" data-modal-size="modal-lg" title="View Payments" href="' . route('view-receive-payments', $row->id) . '">' . __('common.view_payment') . '</a>' : '');
                    }

                    if ($row->paid_status != 'R') {
                        if ($row->total_paid == 0) {
                            $btn .= userPermission('delete-item-receive-view') ? '<a class="dropdown-item deleteUrl" data-modal-size="modal-md" title="' . __('inventory.delete_item_receive') . '" href="' . route('delete-item-receive-view', $row->id) . '">' . __('common.delete') . '</a>' : '';
                        }

                        if ($row->total_paid > 0) {
                            $btn .= '<a class="dropdown-item deleteUrl" data-modal-size="modal-md" title="Cancel Item Receive" href="' . route('cancel-item-receive-view', $row->id) . '">' . __('common.cancel') . '</a>';
                        }
                    }

                    return $btn . '</div>  </div>';
                })
                ->rawColumns(['action', 'receive_date', 'grand_total', 'total_paid', 'total_due', 'status'])
                ->make(true);

        }

        return null;
    }

    public function studentTransportReportAjax(Request $request)
    {
        if ($request->ajax()) {
            
            $students = SmStudent::with(['studentRecord' => function($query) use($request){
               $query->when(!empty($request->class_id), function($q) use ($request){
                  $q->where('class_id',$request->class_id); 
               })->when(!empty($request->section_id),function($q) use ($request){
                   return $q->where('section_id',$request->section_id);
               });
              } , 'studentRecord.class', 'studentRecord.section', 'parents', 'route', 'vehicle', 'drivers'])
              ->whereHas('vehicle')
              ->when(!empty($request->vechile_id),function($query) use ($request) {
                  $query->where('vechile_id',$request->vechile_id);
              })
              ->when(!empty($request->vechile_id),function($query) use ($request) {
                  $query->where('route_list_id',$request->route_id);
              });
               return DataTables::of($students)
                   ->addIndexColumn()
                   ->addColumn('class_section', function ($row): string {
                       return @$row->student_record->class->class_name . '(' . @$row->student_record->section->section_name . ')';
                   })
                   ->rawColumns(['class_section'])
                   ->make(true);
               
           }
   
           return null;
    }
}
