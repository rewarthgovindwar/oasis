<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use Exception;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use Carbon\Carbon;
use App\SmBaseSetup;
use App\ApiBaseMethod;
use App\SmAcademicYear;
use Barryvdh\DomPDF\PDF;
use App\SmStudentCategory;
use App\SmStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmStudentReportController extends Controller
{
    // this function call others
    public static function classSectionStudent($request)
    {

        // 'un_session_id' => 'required',
        //     'un_faculty_id' => 'required',
        //     'un_department_id' => 'required',
        //     'un_academic_id' => 'required',
        //     'un_semester_id' => 'required',
        //     'un_semester_label_id' => 'required',
        //     'un_section_id' => 'required',
        if (moduleStatusCheck('University')) {
                return StudentRecord::query()->when($request->un_academic_id, function ($query) use ($request): void {
                    $query->where('un_academic_id', $request->un_academic_id);
                })
                ->when($request->un_faculty_id, function ($query) use ($request): void {
                    $query->where('un_faculty_id', $request->un_faculty_id);
                })
                ->when($request->un_department_id, function ($query) use ($request): void {
                    $query->where('un_department_id', $request->un_department_id);
                })
                ->when($request->un_semester_id, function ($query) use ($request): void {
                    $query->where('un_semester_id', $request->un_semester_id);
                })
                ->when($request->un_semester_label_id, function ($query) use ($request): void {
                    $query->where('un_semester_label_id', $request->un_semester_label_id);
                })
                ->when($request->un_section_id, function ($query) use ($request): void {
                    $query->where('un_section_id', $request->un_section_id);
                })
                ->where('school_id', auth()->user()->school_id)
                ->where('is_promote', 0)
                ->pluck('student_id')
                ->unique();

        }else{

            return StudentRecord::query()->when($request->academic_year, function ($query) use ($request): void {
                $query->where('academic_id', $request->academic_year);
            })
                ->when($request->class, function ($query) use ($request): void {
                    $query->where('class_id', $request->class);
                })
                ->when($request->section, function ($query) use ($request): void {
                    $query->where('section_id', $request->section);
                })
                ->when(! $request->academic_year, function ($query): void {
                    $query->where('academic_id', getAcademicId());
                })->where('school_id', auth()->user()->school_id)->where('is_promote', 0)->pluck('student_id')->unique();

        }
        
    }

    public static function saasClassSectionStudent($request)
    {
        return StudentRecord::withoutGlobalScopes()->when($request->academic_year, function ($query) use ($request): void {
            $query->where('academic_id', $request->academic_year);
        })
            ->when($request->class, function ($query) use ($request): void {
                $query->where('class_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request): void {
                $query->where('section_id', $request->section);
            })
            ->when(! $request->academic_year, function ($query): void {
                $query->where('academic_id', SmAcademicYear::API_ACADEMIC_YEAR(auth()->user()->school_id));
            })->where('school_id', auth()->user()->school_id)->where('is_promote', 0)->pluck('student_id')->unique();
    }

    public static function classSectionAlumni($request)
    {
        return StudentRecord::query()->when($request->academic_year, function ($query) use ($request): void {
            $query->where('academic_id', $request->academic_year);
        })
            ->when($request->class, function ($query) use ($request): void {
                $query->where('class_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request): void {
                $query->where('section_id', $request->section);
            })
            ->when(! $request->academic_year, function ($query): void {
                $query->where('academic_id', getAcademicId());
            })->where('school_id', auth()->user()->school_id)->where('is_graduate', 1)->where('is_promote', 1)->pluck('student_id')->unique();
    }

    public static function SemesterLabelSectionStudent($request)
    {
        return StudentRecord::query()->when($request->academic_year, function ($query) use ($request): void {
            $query->where('un_academic_id', $request->academic_year);
        })
            ->when($request->un_semester_label_id, function ($query) use ($request): void {
                $query->where('un_semester_label_id', $request->un_semester_label_id);
            })
            ->when($request->un_section_id, function ($query) use ($request): void {
                $query->where('un_section_id', $request->un_section_id);
            })
            ->when(! $request->academic_year, function ($query): void {
                $query->where('un_academic_id', getAcademicId());
            })->where('school_id', auth()->user()->school_id)->where('is_promote', 0)->pluck('student_id')->unique();
    }

    // studentReport modified by jmrashed
    public function studentReport(Request $request)
    {
        /*
        try {
        */
        $classes = SmClass::get();
        $types = SmStudentCategory::get();
        $genders = SmBaseSetup::where('base_group_id', '=', '1')->get();

        return view('backEnd.studentInformation.student_report', ['classes' => $classes, 'types' => $types, 'genders' => $genders]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    // student report search modified by jmrashed
    public function studentReportSearch(Request $request)
    {
        if (moduleStatusCheck('University')) {
            $request->validate([
                'un_session_id' => 'required',
            ]);
        } else {
            $request->validate([
                'class_id' => 'required',
            ]);
        }

        /*
        try {
        */
        $data = [];
        $student_records = StudentRecord::query();
        $student_records->where('school_id', Auth::user()->school_id)->whereHas('studentDetail', function ($q): void {
            $q->where('active_status', 1);
        });
        if ($request->class_id) {
            $student_records->where('class_id', $request->class_id);
        }

        if ($request->section_id) {
            $student_records->where('section_id', $request->section_id);
        }

        if ($request->shift) {
            $student_records->where('shift_id', $request->shift);
        }

        if (moduleStatusCheck('University')) {
            $student_records = universityFilter($student_records, $request);
        }

        $students = $student_records->with(['student' => function ($q) use ($request): void {
            $q->when($request->type, function ($q) use ($request): void {
                $q->where('student_category_id', $request->type);
            })->when($request->gender, function ($q) use ($request): void {
                $q->where('gender_id', $request->gender);
            })->where('active_status', 1);
        }])->whereHas('student', function ($q) use ($request): void {
            $q->when($request->type, function ($q) use ($request): void {
                $q->where('student_category_id', $request->type);
            })->when($request->gender, function ($q) use ($request): void {
                $q->where('gender_id', $request->gender);
            })->where('active_status', 1);
        })->get();

        $data['student_records'] = $students;
        $data['classes'] = SmClass::get();
        $data['types'] = SmStudentCategory::get();
        $data['genders'] = SmBaseSetup::where('base_group_id', '=', '1')->get();
        $data['gender_id'] = $request->gender;
        $selected['class_id'] = $request->class_id;
        $selected['section_id'] = $request->section_id;
        $selected['shift_id'] = shiftEnable() ? $request->shift : '';
        $data['type_id'] = $request->type;
        if (moduleStatusCheck('University')) {
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data += $interface->getCommonData($request);
        }

        // return view('backEnd.studentInformation.student_report', [$data, $selected]);
        return view('backEnd.studentInformation.student_report', array_merge($data, $selected));
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentAttendanceReport(Request $request)
    {
        /*
        try {
        */
        if (teacherAccess()) {
            $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
            $classes = $teacher_info->classes;
        } else {
            $classes = SmClass::get();
        }

        $types = SmStudentCategory::get();
        $genders = SmBaseSetup::where('base_group_id', '=', '1')->get();

        return view('backEnd.studentInformation.student_attendance_report', ['classes' => $classes, 'types' => $types, 'genders' => $genders]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentAttendanceReportSearch(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'month' => 'required',
            'year' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        /*
        try {
        */
        $year = $request->year;
        $month = $request->month;
        $class_id = $request->class;
        $section_id = $request->section;
        $current_day = date('d');
        $clas = SmClass::findOrFail($request->class);
        $sec = SmSection::findOrFail($request->section);
        $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
        if (teacherAccess()) {
            $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
            $classes = $teacher_info->classes;
        } else {
            $classes = SmClass::get();
        }

        $students = SmStudent::where('class_id', $request->class)
            ->where('section_id', $request->section)->get();

        $attendances = [];
        foreach ($students as $student) {
            $attendance = SmStudentAttendance::where('student_id', $student->id)->where('attendance_date', 'like', $request->year.'-'.$request->month.'%')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if (count($attendance) !== 0) {
                $attendances[] = $attendance;
            }
        }

        return view('backEnd.studentInformation.student_attendance_report', ['classes' => $classes, 'attendances' => $attendances, 'students' => $students, 'days' => $days, 'year' => $year, 'month' => $month, 'current_day' => $current_day, 'class_id' => $class_id, 'section_id' => $section_id, 'clas' => $clas, 'sec' => $sec]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentAttendanceReportPrint($class_id, $section_id, string $month, string $year)
    {
        set_time_limit(2700);
        /*
        try {
        */
        $current_day = date('d');
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $students = DB::table('sm_students')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->get();

        $attendances = [];
        foreach ($students as $student) {
            $attendance = SmStudentAttendance::where('student_id', $student->id)
                ->where('attendance_date', 'like', $year.'-'.$month.'%')
                ->get();

            if ($attendance) {
                $attendances[] = $attendance;
            }
        }

        // $pdf = Pdf::loadView(
        //     'backEnd.studentInformation.student_attendance_print',
        //     [
        //         'attendances' => $attendances,
        //         'days' => $days,
        //         'year' => $year,
        //         'month' => $month,
        //         'class_id' => $class_id,
        //         'section_id' => $section_id,
        //         'class' => SmClass::find($class_id),
        //         'section' => SmSection::find($section_id),
        //     ]
        // )->setPaper('A4', 'landscape');
        // return $pdf->stream('student_attendance.pdf');

        $class = SmClass::find($class_id);
        $section = SmSection::find($section_id);

        return view('backEnd.studentInformation.student_attendance_print', ['class' => $class, 'section' => $section, 'attendances' => $attendances, 'days' => $days, 'year' => $year, 'month' => $month, 'current_day' => $current_day, 'class_id' => $class_id, 'section_id' => $section_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function guardianReport(Request $request)
    {
        /*
        try {
        */
        $classes = SmClass::get(['class_name', 'id']);

        return view('backEnd.studentInformation.guardian_report', ['classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function guardianReportSearch(Request $request)
    {
        $input = $request->all();
        if (moduleStatusCheck('University')) {
            $validator = Validator::make($input, [
                'un_session_id' => 'required',
            ]);
        } else {
            $validator = Validator::make($input, [
                'class_id' => 'required',
            ], [
                'class_id' => 'The Class field is required.',
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        /*
        try {
        */
        if (moduleStatusCheck('University')) {
            $student_records = StudentRecord::with('student', 'student.parents', 'UnSemesterLabel', 'unDepartment', 'class:id,class_name', 'section:id,section_name');
        } else {
            $student_records = StudentRecord::with('student', 'student.parents', 'class:id,class_name', 'section:id,section_name');
        }

        $student_records->where('school_id', Auth::user()->school_id);
        if ($request->class_id) {
            $student_records->where('class_id', $request->class_id);
        }

        if ($request->section_id) {
            $student_records->where('section_id', $request->section_id);
        }

        if ($request->shift) {
            $student_records->where('shift_id', $request->shift);
        }

        if (moduleStatusCheck('University')) {
            $student_records = universityFilter($student_records, $request);
        }

        $students = $student_records->select(['id', 'student_id', 'class_id', 'section_id'])->get();
        $data = [];
        $data['student_records'] = $students;
        $data['shift_id'] = shiftEnable() ? $request->shift : '';
        $data['classes'] = SmClass::get(['class_name', 'id']);

        $data['class_id'] = $request->class_id;
        $data['section_id'] = $request->section_id;
        

        return view('backEnd.studentInformation.guardian_report')->with($data);
        /*
        } catch (Exception $exception) {
            dd($exception);
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentLoginReport(Request $request)
    {
        /*
        try {
        */
        $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get(['class_name', 'id']);

        return view('backEnd.studentInformation.login_info', ['classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentLoginSearch(Request $request)
    {

        $request->all();
        if (moduleStatusCheck('University')) {
            $request->validate([
                'un_session_id' => 'required',
            ]);
        } else {
            $request->validate([
                'class' => 'required',
            ]);
        }
        /*
        try {
        */
        $data = [];
        $student_records = StudentRecord::query();
        $student_records->where('school_id', Auth::user()->school_id);
        if ($request->class) {
            $student_records->where('class_id', $request->class);
        }

        if ($request->section) {
            $student_records->where('section_id', $request->section);
        }
       
        if ($request->shift) {
            $student_records->where('shift_id', $request->shift);
        }

        if (moduleStatusCheck('University')) {
            $student_records = universityFilter($student_records, $request);
        }

        $students = $student_records->with('student.user', 'student.parents')->get();
        $data['student_records'] = $students;
        $data['classes'] = SmClass::get();
        $data['class_id'] = $request->class;
        $data['section_id'] = $request->section;
        $data['shift_id'] = shiftEnable() ? $request->shift : '';
        $data['clas'] = SmClass::find($request->class);
        if (moduleStatusCheck('University')) {
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data += $interface->getCommonData($request);
        }

        return view('backEnd.studentInformation.login_info', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentHistory(Request $request)
    {
        /*
        try {
        */
        $classes = SmClass::get(['id', 'class_name']);
        $years = SmStudent::select('admission_date')->where('active_status', 1)
            ->where('academic_id', getAcademicId())->get()
            ->groupBy(function ($val): string {
                return Carbon::parse($val->admission_date)->format('Y');
            });

        return view('backEnd.studentInformation.student_history', ['classes' => $classes, 'years' => $years]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentHistorySearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        /*
        try {
        */
        $academic_id = getAcademicId();
        $user = Auth::user();
        $student_ids = static::classSectionStudent($request);
        $classes = SmClass::where('active_status', 1)->where('academic_id', $academic_id)->where('school_id', $user->school_id)->get(['id', 'class_name']);
        $students = SmStudent::where('academic_id', $academic_id)
            ->where('active_status', 1);
        if ($request->admission_year !== '') {
            $students = $students->where('admission_date', 'like', $request->admission_year.'%');
        }

        $students = $students->whereIn('id', $student_ids)
            ->with('recordClass.class', 'parents:id,guardians_name,guardians_mobile', 'promotion', 'session')
            ->where('academic_id', $academic_id)
            ->where('school_id', $user->school_id)
            ->select(['admission_no', 'id', 'class_id', 'section_id', 'parent_id', 'mobile', 'admission_date', 'first_name', 'last_name'])
            ->get();

        $years = SmStudent::select('admission_date')->where('active_status', 1)
            ->where('academic_id', $academic_id)->get()
            ->groupBy(function ($val): string {
                return Carbon::parse($val->admission_date)->format('Y');
            });
        $class_id = $request->class;
        $shift_id = shiftEnable() ? $request->shift : '';
        $year = $request->admission_year;
        $student_id = null;

        $clas = SmClass::find($request->class);

        return view('backEnd.studentInformation.student_history', ['students' => $students, 'classes' => $classes, 'years' => $years, 'class_id' => $class_id, 'year' => $year, 'clas' => $clas, 'student_id' => $student_id, 'shift_id' => $shift_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
