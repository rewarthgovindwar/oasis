<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use Exception;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;
use App\Http\Requests\Admin\StudentInfo\StudentAttendanceReportSearchRequest;
use App\Models\Shift;

class SmStudentAttendanceReportController extends Controller
{
    //


    public function index(Request $request)
    {
        /*
        try {
        */
            if (teacherAccess()) {
                $teacher_info = SmStaff::with('classes')->where('user_id', Auth::user()->id)->first();
                $classes = $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }

            return view('backEnd.studentInformation.student_attendance_report', ['classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function search(StudentAttendanceReportSearchRequest $studentAttendanceReportSearchRequest)
    {

        /*
        try {
        */
            // return $request->all();
            $data = [];
            $year = $studentAttendanceReportSearchRequest->year;
            $month = $studentAttendanceReportSearchRequest->month;
            $class_id = $studentAttendanceReportSearchRequest->class;
            $section_id = $studentAttendanceReportSearchRequest->section;
            $shift_id = shiftEnable() ? $studentAttendanceReportSearchRequest->shift : '';
            $current_day = date('d');
            $class = null;
            $section = null;
            $user = Auth::user();
            $academic_id = getAcademicId();
            if (! moduleStatusCheck('University')) {
                $class = SmClass::findOrFail($studentAttendanceReportSearchRequest->class);
                $section = SmSection::findOrFail($studentAttendanceReportSearchRequest->section);
            }

            $days = cal_days_in_month(CAL_GREGORIAN, $studentAttendanceReportSearchRequest->month, $studentAttendanceReportSearchRequest->year);
            if (teacherAccess()) {
                $teacher_info = SmStaff::with('classes:class_name,id')->where('user_id', $user->id)->first();
                $classes = $teacher_info->classes;
            } else {
                $classes = SmClass::get(['id', 'class_name']);
            }

            $students = StudentRecord::where('class_id', $studentAttendanceReportSearchRequest->class)
                ->where('section_id', $studentAttendanceReportSearchRequest->section)
                ->when(shiftEnable(), function ($query) use ($studentAttendanceReportSearchRequest) {
                    return $query->where('shift_id', $studentAttendanceReportSearchRequest->shift);
                })
                ->where('academic_id', $academic_id)
                ->where('school_id', $user->school_id)->get()->sortBy('roll_no');

            if (moduleStatusCheck('University')) {
                $data['un_semester_label_id'] = $studentAttendanceReportSearchRequest->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->oldValueSelected($studentAttendanceReportSearchRequest);
                $model = StudentRecord::query();
                $students = universityFilter($model, $studentAttendanceReportSearchRequest)->get()->sortBy('roll_no');
                // return $data;
            }

            $attendances = [];
            foreach ($students as $student) {
                $attendance = SmStudentAttendance::where('student_id', $student->student_id)
                    ->where('attendance_date', 'like', $studentAttendanceReportSearchRequest->year.'-'.$studentAttendanceReportSearchRequest->month.'%')
                    ->where('academic_id', $academic_id)->where('school_id', $user->school_id)
                    ->where('student_record_id', $student->id)
                    ->get(['attendance_type', 'id', 'attendance_date', 'student_id']);
                if (count($attendance) !== 0) {
                    $attendances[] = $attendance;
                }
            }

            return view('backEnd.studentInformation.student_attendance_report', ['classes' => $classes, 'attendances' => $attendances, 'students' => $students, 'days' => $days, 'year' => $year, 'month' => $month, 'current_day' => $current_day, 'class_id' => $class_id, 'section_id' => $section_id, 'class' => $class, 'section' => $section, 'shift_id' => $shift_id])->with($data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function print($class_id, $section_id, string $month, string $year, $shift_id = null)
    {
        set_time_limit(2700);
        $user = Auth::user();
        $academic_id = getAcademicId();

        /*
        try {
        */
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            // $active_students = SmStudent::where('active_status', 1)->where('school_id', Auth::user()->school_id)->pluck('id')->toArray();
            // $students = DB::table('student_records')
            // ->where('class_id', $class_id)
            // ->where('section_id', $section_id)
            // ->where('academic_id', getAcademicId())
            // ->whereIn('student_id', $active_students)
            // ->where('school_id', Auth::user()->school_id)->sortBy('roll_no')
            // ->get();
            $students = StudentRecord::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('academic_id', $academic_id)
                ->when($shift_id, function ($query, $shift_id) {
                    return $query->where('shift_id', $shift_id);
                })
                ->where('school_id', $user->school_id)->get()->sortBy('roll_no');

            $attendances = [];
            foreach ($students as $student) {
                $attendance = SmStudentAttendance::where('student_id', $student->student_id)
                    ->where('attendance_date', 'like', $year.'-'.$month.'%')
                    ->where('academic_id', $academic_id)
                    ->where('school_id', $user->school_id)
                    ->where('student_record_id', $student->id)
                    ->get(['attendance_type', 'id', 'attendance_date', 'student_id']);

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
            $shift= null;
            if ($shift_id != null) {
                $shift = Shift::find($shift_id);
            }

            return view('backEnd.studentInformation.student_attendance_print', ['class' => $class, 'section' => $section, 'attendances' => $attendances, 'days' => $days, 'year' => $year, 'month' => $month, 'current_day' => $current_day, 'class_id' => $class_id, 'section_id' => $section_id, 'shift' => $shift]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function unPrint($semester_id, string $month, string $year)
    {
        set_time_limit(2700);
        $user = Auth::user();
        $academic_id = getAcademicId();

        
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $active_students = SmStudent::where('active_status', 1)
                ->where('school_id', $user->school_id)
                ->where('academic_id', getAcademicId())
                ->pluck('id')->toArray();
            $students = DB::table('student_records')
                ->where('un_semester_label_id', $semester_id)
            // ->where('academic_id', getAcademicId())
                ->whereIn('student_id', $active_students)
                ->where('school_id', $user->school_id)
                ->get();

            $attendances = [];
            foreach ($students as $student) {
                $attendance = SmStudentAttendance::where('student_id', $student->student_id)
                    ->where('attendance_date', 'like', $year.'-'.$month.'%')
                    ->where('school_id', $user->school_id)
                    ->where('academic_id', $academic_id)
                    ->where('student_record_id', $student->id)
                    ->get();

                if ($attendance) {
                    $attendances[] = $attendance;
                }
            }

            $request = (object) [
                'un_session_id' => null,
                'un_faculty_id' => null,
                'un_department_id' => null,
                'un_academic_id' => null,
                'un_semester_id' => null,
                'un_semester_label_id' => $semester_id,
            ];
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data = $interface->searchInfo($request);
            

            return view('backEnd.studentInformation.student_attendance_print', ['attendances' => $attendances, 'days' => $days, 'year' => $year, 'month' => $month, 'current_day' => $current_day, 'semester_id' => $semester_id])->with($data);
       
    }
}
