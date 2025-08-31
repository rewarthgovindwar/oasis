<?php

namespace App\Http\Controllers\Admin\Examination;

use App\User;
use Exception;
use App\SmExam;
use App\SmClass;
use App\SmStaff;
use App\SmHoliday;
use App\SmSection;
use App\SmSubject;
use App\SmExamType;
use App\SmClassRoom;
use App\SmClassTime;
use App\SmExamSetup;
use App\Models\Shift;
use App\SmAcademicYear;
use App\SmExamSchedule;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\NotificationSend;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\University\Entities\UnFaculty;
use Modules\University\Entities\UnSession;
use Modules\University\Entities\UnSemester;
use Modules\University\Entities\UnDepartment;
use Modules\University\Entities\UnAcademicYear;
use Modules\University\Entities\UnSemesterLabel;
use App\Http\Requests\Admin\Examination\SmExamScheduleSearchRequest;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmExamRoutineController extends Controller
{
    use NotificationSend;


    public function examSchedule()
    {
        /*
        try {
        */
            $exam_types = SmExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.class_id')
                    ->where('sm_assign_subjects.academic_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.school_id', Auth::user()->school_id)
                    ->select('sm_classes.id', 'class_name')
                    ->distinct('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }

            return view('backEnd.examination.exam_schedule', ['classes' => $classes, 'exam_types' => $exam_types]);
        /*
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examScheduleCreate()
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

            $sections = SmSection::get();
            $subjects = SmSubject::get();
            $exams = SmExam::get();
            $exam_types = SmExamType::get();

            return view('backEnd.examination.exam_schedule_create', ['classes' => $classes, 'exams' => $exams, 'exam_types' => $exam_types]);
        /*
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function addExamRoutineModal($subject_id, $exam_period_id, $class_id, $section_id, $exam_term_id, $section_id_all)
    {
        /*
        try {
        */
            $rooms = SmClassRoom::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.examination.add_exam_routine_modal', ['subject_id' => $subject_id, 'exam_period_id' => $exam_period_id, 'class_id' => $class_id, 'section_id' => $section_id, 'exam_term_id' => $exam_term_id, 'rooms' => $rooms, 'section_id_all' => $section_id_all]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function checkExamRoutinePeriod(Request $request)
    {

        /*
        try {
        */
            $exam_period_check = SmExamSchedule::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('exam_period_id', $request->exam_period_id)
                ->where('exam_term_id', $request->exam_term_id)
                ->where('date', date('Y-m-d', strtotime($request->date)))
                ->where('school_id', Auth::user()->school_id)
                ->first();

            return response()->json(['exam_period_check' => $exam_period_check]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function updateExamRoutinePeriod(Request $request)
    {

        /*
        try {
        */
            $update_exam_period_check = SmExamSchedule::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('exam_period_id', $request->exam_period_id)
                ->where('exam_term_id', $request->exam_term_id)
                ->where('date', date('Y-m-d', strtotime($request->date)))
                ->where('school_id', Auth::user()->school_id)
                ->first();

            return response()->json(['update_exam_period_check' => $update_exam_period_check]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function EditExamRoutineModal($subject_id, $exam_period_id, $class_id, $section_id, $exam_term_id, $assigned_id, $section_id_all)
    {

        /*
        try {
        */
            $rooms = SmClassRoom::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $assigned_exam = SmExamSchedule::find($assigned_id);

            return view('backEnd.examination.add_exam_routine_modal', ['subject_id' => $subject_id, 'exam_period_id' => $exam_period_id, 'class_id' => $class_id, 'section_id' => $section_id, 'exam_term_id' => $exam_term_id, 'rooms' => $rooms, 'assigned_exam' => $assigned_exam, 'section_id_all' => $section_id_all]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteExamRoutineModal($assigned_id, $section_id_all)
    {

        /*
        try {
        */
            return view('backEnd.examination.delete_exam_routine', ['assigned_id' => $assigned_id, 'section_id_all' => $section_id_all]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function checkExamRoutineDate(Request $request)
    {

        /*
        try {
        */
            if ($request->assigned_id == '') {
                $check_date = SmExamSchedule::where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('exam_term_id', $request->exam_term_id)->where('date', date('Y-m-d', strtotime($request->date)))->where('exam_period_id', $request->exam_period_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            } else {
                $check_date = SmExamSchedule::where('id', '!=', $request->assigned_id)->where('class_id', $request->class_id)->where('section_id', $request->section_id)->where('exam_term_id', $request->exam_term_id)->where('date', date('Y-m-d', strtotime($request->date)))->where('exam_period_id', $request->exam_period_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            }

            $holiday_check = SmHoliday::where('from_date', '<=', date('Y-m-d', strtotime($request->date)))->where('to_date', '>=', date('Y-m-d', strtotime($request->date)))->where('school_id', Auth::user()->school_id)->first();

            if ($holiday_check !== '') {
                $from_date = date('jS M, Y', strtotime($holiday_check->from_date));
                $to_date = date('jS M, Y', strtotime($holiday_check->to_date));
            } else {
                $from_date = '';
                $to_date = '';
            }

            return response()->json([$check_date, $holiday_check, $from_date, $to_date]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examScheduleReportSearch(SmExamScheduleSearchRequest $smExamScheduleSearchRequest)
    {
        /*
        try {
        */
            if (moduleStatusCheck('University')) {

                $un_session = UnSession::find($smExamScheduleSearchRequest->un_session_id);
                $un_faculty = UnFaculty::find($smExamScheduleSearchRequest->un_faculty_id);
                $un_department = UnDepartment::find($smExamScheduleSearchRequest->un_department_id);
                $un_academic = UnAcademicYear::find($smExamScheduleSearchRequest->un_academic_id);
                $un_semester = UnSemester::find($smExamScheduleSearchRequest->un_semester_id);
                $un_semester_label = UnSemesterLabel::find($smExamScheduleSearchRequest->un_semester_label_id);
                $un_section = SmSection::find($smExamScheduleSearchRequest->un_section_id);

                $examName = SmExamType::where('id', $smExamScheduleSearchRequest->exam_type)
                    ->first()
                    ->title;

                $SmExamSchedule = SmExamSchedule::query();
                $exam_schedules = universityFilter($SmExamSchedule, $smExamScheduleSearchRequest)
                    ->where('exam_term_id', $smExamScheduleSearchRequest->exam_type)
                    ->with('subjectDetails')
                    ->get();

                $data['un_semester_label_id'] = $smExamScheduleSearchRequest->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data = $interface->oldValueSelected($smExamScheduleSearchRequest);

                return view('backEnd.examination.exam_schedule', ['exam_schedules' => $exam_schedules, 'un_session' => $un_session, 'un_faculty' => $un_faculty, 'un_department' => $un_department, 'un_academic' => $un_academic, 'un_semester' => $un_semester, 'un_semester_label' => $un_semester_label, 'un_section' => $un_section, 'examName' => $examName])->with($data);
            }

            $assign_subjects = SmAssignSubject::query();
            if (! empty($smExamScheduleSearchRequest->section)) {
                $assign_subjects->where('section_id', $smExamScheduleSearchRequest->section);
            }
            if (!empty($smExamScheduleSearchRequest->shift)) {
                $assign_subjects->where('shift_id', $smExamScheduleSearchRequest->shift);
            }


            $assign_subjects = $assign_subjects->where('class_id', $smExamScheduleSearchRequest->class)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if ($assign_subjects->count() == 0) {
                Toastr::error('No Subject Assigned. Please assign subjects in this class.', 'Failed');

                return redirect()->back();
                // return redirect('exam-schedule-create')->with('message-danger', 'No Subject Assigned. Please assign subjects in this class.');
            }

            $assign_subjects = SmAssignSubject::query();
            if (! empty($smExamScheduleSearchRequest->section)) {
                $assign_subjects->where('section_id', $smExamScheduleSearchRequest->section);
            }
            if (!empty($smExamScheduleSearchRequest->shift)) {
                $assign_subjects->where('shift_id', $smExamScheduleSearchRequest->shift);
            }

            $assign_subjects = $assign_subjects->where('class_id', $smExamScheduleSearchRequest->class)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exams = SmExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_id = $smExamScheduleSearchRequest->class;
            $section_id = empty($smExamScheduleSearchRequest->section) ? 0 : $smExamScheduleSearchRequest->section;

            $exam_id = $smExamScheduleSearchRequest->exam_type;
            $shift_id = shiftEnable() ? $smExamScheduleSearchRequest->shift : '';

            $exam_types = SmExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $exam_schedules = SmExamSchedule::query();
            if (! empty($smExamScheduleSearchRequest->section)) {
                $exam_schedules->where('section_id', $smExamScheduleSearchRequest->section);
            }
            if (!empty($smExamScheduleSearchRequest->shift)) {
                $exam_schedules->where('shift_id', $smExamScheduleSearchRequest->shift);
            }

            $exam_schedules = $exam_schedules->where('exam_term_id', $exam_id)
                ->where('class_id', $smExamScheduleSearchRequest->class)
                ->where('school_id', Auth::user()->school_id)
                ->get();
            

            //  return $exam_schedules;

            $exam_type_id = $smExamScheduleSearchRequest->exam_type;

            $examName = SmExamType::where('id', $smExamScheduleSearchRequest->exam_type)->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->first()->title;
            $search_current_class = SmClass::find($smExamScheduleSearchRequest->class);
            $search_current_section = SmSection::find($smExamScheduleSearchRequest->section);
            $search_current_shift = shiftEnable() ? Shift::find($smExamScheduleSearchRequest->shift) : null;

            return view('backEnd.examination.exam_schedule_new', ['classes' => $classes, 'exams' => $exams, 'exam_schedules' => $exam_schedules, 'assign_subjects' => $assign_subjects, 'class_id' => $class_id, 'shift_id' => $shift_id, 'section_id' => $section_id, 'exam_id' => $exam_id, 'exam_types' => $exam_types, 'exam_type_id' => $exam_type_id, 'examName' => $examName, 'search_current_class' => $search_current_class, 'search_current_section' => $search_current_section, 'search_current_shift' => $search_current_shift]);

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function compareByTimeStamp($time1, $time2)
    {

        /*
        try {
        */
            if (strtotime($time1) < strtotime($time2)) {
                return 1;
            }

            if (strtotime($time1) > strtotime($time2)) {
                return -1;
            }

            return 0;

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examScheduleReportSearchOld(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);

        /*
        try {
        */
            $assign_subjects = SmAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)
            ->when(shiftEnable(), function ($query) use ($request) {
                return $query->where('shift_id', $request->shift);
            })
            ->where('school_id', Auth::user()->school_id)->get();

            if ($assign_subjects->count() == 0) {
                Toastr::success('No Subject Assigned. Please assign subjects in this class.', 'Success');

                return redirect('exam-schedule-create');
            }

            $assign_subjects = SmAssignSubject::where('class_id', $request->class)->where('section_id', $request->section)
            ->when(shiftEnable(), function ($query) use ($request) {
                return $query->where('shift_id', $request->shift);
            })
            ->where('school_id', Auth::user()->school_id)->get();

            $classes = SmClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $exams = SmExam::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

            $class_id = $request->class;
            $section_id = $request->section;
            $exam_id = $request->exam;
            $shift_id = shiftEnable() ? $request->shift : '';

            $exam_types = SmExamType::all();
            $exam_periods = SmClassTime::where('type', 'exam')->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.examination.exam_schedule', ['classes' => $classes, 'exams' => $exams, 'shift_id' => $shift_id, 'assign_subjects' => $assign_subjects, 'class_id' => $class_id, 'section_id' => $section_id, 'exam_id' => $exam_id, 'exam_types' => $exam_types, 'exam_periods' => $exam_periods]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examSchedulePrint(Request $request)
    {

        /*
        try {
        */
            $assign_subjects = SmAssignSubject::query();

            if ($request->section_id !== 0) {
                $assign_subjects->where('section_id', $request->section_id);
            }

            $assign_subjects = $assign_subjects->where('class_id', $request->class_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->distinct(['section_id', 'subject_id'])
                ->get();

            $exam_periods = SmClassTime::where('type', 'exam')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $academic_year = SmAcademicYear::find(getAcademicId());

            $class_id = $request->class_id;

            // if($request->section_id==0){
            //     $section_id='All Sections';
            // }else{
            //     $section_id = $request->section_id;
            // }
            $exam_id = $request->exam_id;
            $print = request()->print;

            return view(
                'backEnd.examination.exam_schedult_print',
                [
                    'assign_subjects' => $assign_subjects,
                    'exam_periods' => $exam_periods,
                    'class_id' => $request->class_id,
                    'academic_year' => $academic_year,

                    'section_id' => $request->section_id,
                    'exam_id' => $request->exam_id,
                    'print' => $print,
                ]
            );

            //            $pdf = Pdf::loadView(
            //                'backEnd.examination.exam_schedult_print',
            //                [
            //                    'assign_subjects' => $assign_subjects,
            //                    'exam_periods' => $exam_periods,
            //                    'class_id' => $request->class_id,
            //                    'academic_year' => $academic_year,
            //
            //                    'section_id' => $request->section_id,
            //                    'exam_id' => $request->exam_id,
            //                ]
            //            )->setPaper('A4', 'landscape');
            //            return $pdf->stream('EXAM_SCHEDULE.pdf');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examRoutineReport(Request $request)
    {

        /*
        try {
        */
            $auth = Auth::user();
            $exam_types = SmExamType::select(['id', 'title'])->where('school_id', $auth->school_id)->where('academic_id', getAcademicId())->get();
            $classes = SmClass::select(['id', 'class_name'])->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', $auth->school_id)->get();

            return view('backEnd.reports.exam_routine_report', ['classes' => $classes, 'exam_types' => $exam_types]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function universityExamRoutineReportSearch($request)
    {
        $request->validate([
            'exam' => 'required',
            'un_semester_label_id' => 'required',
            'un_section_id' => 'sometimes|nullable',
        ]);

        /*
        try {
        */
            $auth = Auth::user();
            $schoolId = $auth->school_id;
            $academicId = getAcademicId();

            $class_id = $request->un_semester_label_id;
            $section_id = $request->un_section_id;
            $exam_id = $request->exam;


            // Fetch required exam types in one query
            $exam_types = SmExamType::where('school_id', $schoolId)
                ->where('academic_id', $academicId)
                ->get(['id', 'title']);

            // Fetch exam name in a single query
            $examName = SmExamType::find($exam_id)?->title ?? 'Unknown';

            // Optimize exam schedule query with eager loading
            $exam_schedules = SmExamSchedule::where('school_id', $schoolId)
                ->when($exam_id, fn ($query) => $query->where('exam_term_id', $exam_id))
                ->when($class_id, fn ($query) => $query->where('un_semester_label_id', $class_id))
                ->when($section_id, fn ($query) => $query->where('un_section_id', $section_id))
                ->with([
                    'subject:id,subject_code,subject_name',
                    'unSemesterLabel:id,name',
                    'teacher:id,full_name',
                    'classRoom:id,room_no',
                ])
                ->select(['id', 'date', 'start_time', 'end_time', 'un_section_id', 'un_semester_label_id', 'exam_term_id', 'subject_id', 'teacher_id', 'room_id'])
                ->get();

            return view('backEnd.reports.exam_routine_report', ['exam_schedules' => $exam_schedules, 'class_id' => $class_id, 'section_id' => $section_id, 'exam_type_id' => $exam_id, 'examName' => $examName, 'exam_types' => $exam_types]);

            $examName     = SmExamType::where('id', $request->exam)->first()->title;

            return view('backEnd.reports.exam_routine_report', compact('exam_schedules', 'class_id', 'section_id', 'exam_id', 'exam_type_id', 'examName', 'exam_types'));
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examRoutineReportSearch(Request $request)
    {
        if (moduleStatusCheck('University')) {
            return $this->universityExamRoutineReportSearch($request);
        }

        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'sometimes|nullable',
        ]);

        /*
            try {
            */

            $academicId = getAcademicId();
            $auth = Auth::user();
            $classes = SmClass::select(['id', 'class_name'])->get();
            $exams = SmExam::get();
            $class_id = $request->class ?: 0;
            $section_id = $request->section ?: 0;
            $exam_id = $request->exam;
            $shift_id = $request->shift ? $request->shift : 0;

            $exam_types = SmExamType::where('school_id', $auth->school_id)
                ->where('academic_id', $academicId)
                ->get(['id', 'title']);

            $exam_schedules = SmExamSchedule::where('school_id', $auth->school_id)
                ->when($exam_id, fn ($query) => $query->where('exam_term_id', $exam_id))
                ->when($class_id, fn ($query) => $query->where('class_id', $class_id))
                ->when($section_id, fn ($query) => $query->where('section_id', $section_id))
                ->when($shift_id, fn ($query) => $query->where('shift_id', $shift_id))
                ->with([
                    'subject:id,subject_code,subject_name',
                    'class:id,class_name',
                    'section:id,section_name',
                    'teacher:id,full_name',
                    'classRoom:id,room_no',
                ])
                ->select(['id', 'date', 'class_id', 'section_id', 'shift_id', 'start_time', 'end_time',  'exam_term_id', 'subject_id', 'teacher_id', 'room_id'])
                ->get();

            $exam_type_id = $request->exam;

            $examName = SmExamType::where('id', $request->exam)->first()->title;
            $search_current_class = SmClass::find($request->class);
            $search_current_section = SmSection::find($request->section);

                return view('backEnd.reports.exam_routine_report', compact('classes', 'exams', 'exam_schedules', 'class_id', 'section_id', 'shift_id', 'exam_id', 'exam_types', 'exam_type_id', 'examName', 'search_current_class', 'search_current_section'));
            /*
            } catch (\Exception $e) {

                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
            */


    }

    public function examRoutineReportSearchPrint($exam_id)
    {

        /*
        try {
        */
            $exam_types = SmExamType::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_routines = SmExamSchedule::where('exam_term_id', $exam_id)->orderBy('date', 'ASC')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_routines = $exam_routines->groupBy('date');
            $academic_year = SmAcademicYear::find(getAcademicId());
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examScheduleSearch(SmExamScheduleSearchRequest $smExamScheduleSearchRequest)
    {
        /*
        try {
        */
            if (moduleStatusCheck('University')) {
                $data = [];
                $exam_type_id = $smExamScheduleSearchRequest->exam_type;

                $un_session = UnSession::find($smExamScheduleSearchRequest->un_session_id);
                $un_faculty = UnFaculty::find($smExamScheduleSearchRequest->un_faculty_id);
                $un_department = UnDepartment::find($smExamScheduleSearchRequest->un_department_id);
                $un_academic = UnAcademicYear::find($smExamScheduleSearchRequest->un_academic_id);
                $un_semester = UnSemester::find($smExamScheduleSearchRequest->un_semester_id);
                $un_semester_label = UnSemesterLabel::find($smExamScheduleSearchRequest->un_semester_label_id);
                $un_section = SmSection::find($smExamScheduleSearchRequest->un_section_id);

                $SmExam = SmExam::query();
                $subjects = universityFilter($SmExam, $smExamScheduleSearchRequest)
                    ->where('exam_type_id', $smExamScheduleSearchRequest->exam_type)
                    ->with('subjectDetails')
                    ->orWhereNull('un_section_id')
                    ->get();

                $teachers = SmStaff::where('role_id', 4)->where('active_status', 1)
                    ->where('school_id', auth()->user()->school_id)
                    ->get(['id', 'user_id', 'full_name']);

                $rooms = SmClassRoom::where('active_status', 1)->where('school_id', auth()->user()->school_id)
                    ->get(['id', 'room_no']);

                $examName = SmExamType::where('id', $smExamScheduleSearchRequest->exam_type)
                    ->where('active_status', 1)
                    ->first()
                    ->title;

                $SmExamSchedule = SmExamSchedule::query();
                $exam_schedule = universityFilter($SmExamSchedule, $smExamScheduleSearchRequest)
                    ->where('exam_term_id', $smExamScheduleSearchRequest->exam_type)
                    ->get();

                $rooms = SmClassRoom::where('active_status', 1)
                    ->where('school_id', auth()->user()->school_id)
                    ->get(['id', 'room_no']);

                $data['un_semester_label_id'] = $smExamScheduleSearchRequest->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data = $interface->oldValueSelected($smExamScheduleSearchRequest);

                return view(
                    'backEnd.examination.exam_schedule_create',
                    ['examName' => $examName, 'un_session' => $un_session, 'un_faculty' => $un_faculty, 'un_department' => $un_department, 'un_academic' => $un_academic, 'un_semester' => $un_semester, 'un_semester_label' => $un_semester_label, 'un_section' => $un_section, 'exam_type_id' => $exam_type_id, 'exam_schedule' => $exam_schedule, 'teachers' => $teachers, 'rooms' => $rooms, 'subjects' => $subjects]
                )->with($data);
            }

            $subject_ids = SmExamSetup::query();
            $assign_subjects = SmAssignSubject::query();

            if ($smExamScheduleSearchRequest->class !== null) {
                $assign_subjects->where('class_id', $smExamScheduleSearchRequest->class);
                $subject_ids->where('class_id', $smExamScheduleSearchRequest->class);
            }

            if ($smExamScheduleSearchRequest->section !== null) {
                $assign_subjects->where('section_id', $smExamScheduleSearchRequest->section);
                $subject_ids->where('section_id', $smExamScheduleSearchRequest->section);
            }
            if ($smExamScheduleSearchRequest->shift != null) {
                $assign_subjects->where('shift_id', $smExamScheduleSearchRequest->shift);
                $subject_ids->where('shift_id', $smExamScheduleSearchRequest->shift);
            }

            $assign_subjects = $assign_subjects->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            
            $subject_ids = $subject_ids->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('exam_term_id', $smExamScheduleSearchRequest->exam_type)
                ->pluck('subject_id')->toArray();

            if ($assign_subjects->count() == 0) {
                Toastr::success('No Subject Assigned. Please assign subjects in this class.', 'Success');

                return redirect('exam-schedule-create');
            }

            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }

            $class_id = $smExamScheduleSearchRequest->class;
            $section_id = $smExamScheduleSearchRequest->section ?? 0;
            $shift_id = $smExamScheduleSearchRequest->shift != null ? $smExamScheduleSearchRequest->shift : 0;
            $exam_type_id = $smExamScheduleSearchRequest->exam_type;
            $exam_types = SmExamType::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exam_schedule = SmExamSchedule::query();
            if ($smExamScheduleSearchRequest->class) {
                $exam_schedule->where('class_id', $smExamScheduleSearchRequest->class);
            }

            if ($smExamScheduleSearchRequest->section) {
                $exam_schedule->where('section_id', $smExamScheduleSearchRequest->section);
            }

            $exam_schedule = $exam_schedule->where('exam_term_id', $smExamScheduleSearchRequest->exam_type)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $subjects = SmSubject::whereIn('id', $subject_ids)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get(['id', 'subject_name']);
            
            $teachers = SmStaff::where('role_id', 4)->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get(['id', 'user_id', 'full_name']);

            $rooms = SmClassRoom::where('active_status', 1)->where('school_id', Auth::user()->school_id)
                ->get(['id', 'room_no']);

            $examName = SmExamType::where('id', $smExamScheduleSearchRequest->exam_type)->where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->first()->title;

            $search_current_class = SmClass::find($smExamScheduleSearchRequest->class);
            $search_current_section = SmSection::find($smExamScheduleSearchRequest->section);
            $class_section = $search_current_class->classSection->where('shift_id', $smExamScheduleSearchRequest->shift);

            return view('backEnd.examination.exam_schedule_new_update', ['classes' => $classes, 'subjects' => $subjects, 'exam_schedule' => $exam_schedule, 'class_id' => $class_id, 'section_id' => $section_id, 'exam_type_id' => $exam_type_id, 'exam_types' => $exam_types, 'teachers' => $teachers, 'rooms' => $rooms, 'examName' => $examName, 'search_current_class' => $search_current_class, 'search_current_section' => $search_current_section, 'shift_id' => $shift_id, 'class_section' => $class_section]);

        /*
        } catch (Exception $exception) {
            Toastr::error('No Result Found!', 'Failed');

            return redirect()->back();
        }
        */
    }

    // end

    public function addExamRoutineStore(Request $request)
    {

        if (db_engine() == 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        /*
        try {
        */
            if (moduleStatusCheck('University')) {
                $exam_type_id = $request->exam_type_id;

                $SmExamSchedule = SmExamSchedule::query();
                $exam_schedule = universityFilter($SmExamSchedule, $request)
                    ->where('exam_term_id', $exam_type_id)
                    ->delete();

                foreach ($request->routine as $routine_data) {

                    $sm_exam = SmExam::where('exam_type_id', $exam_type_id)
                        ->where('un_semester_label_id', $request->un_semester_label_id)
                        ->where('un_subject_id', gv($routine_data, 'subject'))
                        ->first();

                    if (gv($routine_data, 'subject') == 'Select Subject *') {
                        Toastr::error('Subject Can not Be Empty', 'Failed');
                    }

                    if (! gv($routine_data, 'subject')) {
                        continue;
                    }

                    if (gv($routine_data, 'subject') == 'Select Subject *') {
                        continue;
                    }

                    if (! gv($routine_data, 'start_time')) {
                        continue;
                    }

                    if (! gv($routine_data, 'end_time')) {
                        continue;
                    }

                    $is_exist = SmExamSchedule::where(
                        [
                            'exam_term_id' => $exam_type_id,
                            'exam_id' => $sm_exam->id,
                            'subject_id' => gv($routine_data, 'subject'),
                            'date' => date('Y-m-d', strtotime(gv($routine_data, 'date'))),
                            'start_time' => date('H:i:s', strtotime(gv($routine_data, 'start_time'))),
                            'end_time' => date('H:i:s', strtotime(gv($routine_data, 'end_time'))),
                            'room_id' => gv($routine_data, 'room'),
                            'un_session_id' => $request->un_session_id,
                            'un_faculty_id' => $request->un_faculty_id,
                            'un_department_id' => $request->un_department_id,
                            'un_academic_id' => $request->un_academic_id,
                            'un_semester_id' => $request->un_semester_id,
                            'un_semester_label_id' => $request->un_semester_label_id,
                            'un_section_id' => $request->un_section_id,
                        ]
                    )->where('school_id', Auth::user()->school_id)->first();

                    if ($is_exist) {
                        continue;
                    }

                    $exam_routine = new SmExamSchedule();
                    $exam_routine->exam_term_id = $exam_type_id;
                    $exam_routine->exam_id = $sm_exam->id;

                    $common = App::make(UnCommonRepositoryInterface::class);
                    $common->storeUniversityData($exam_routine, $request);

                    $exam_routine->un_subject_id = gv($routine_data, 'subject');
                    $exam_routine->teacher_id = gv($routine_data, 'teacher_id');
                    $exam_routine->date = date('Y-m-d', strtotime(gv($routine_data, 'date')));
                    $exam_routine->start_time = date('H:i:s', strtotime(gv($routine_data, 'start_time')));
                    $exam_routine->end_time = date('H:i:s', strtotime(gv($routine_data, 'end_time')));
                    $exam_routine->room_id = gv($routine_data, 'room');
                    $exam_routine->school_id = Auth::user()->school_id;
                    $exam_routine->academic_id = getAcademicId();
                    $exam_routine->save();
                }

                Toastr::success('Exam routine has been assigned successfully', 'Success');

                return redirect('exam-schedule');
            }

            $validator = Validator::make($request->all(), [
                'class_id' => 'required|integer',
                'section_id' => 'required|integer',
                'exam_type_id' => 'required|integer',
                'routine' => 'required|array',
                'routine.*.subject' => 'required|integer',
                'routine.*.teacher_id' => 'required|integer',
                'routine.*.date' => 'required',
                'routine.*.start_time' => 'required',
                'routine.*.end_time' => 'required',
                'routine.*.room' => 'required|string',
            ]);

            $validator->after(function ($validator) use ($request): void {
                $subjectIds = array_column($request->input('routine'), 'subject');

                if (count($subjectIds) !== count(array_unique($subjectIds))) {
                    Toastr::error('Duplicate subjects are not allowed in the exam routine.', 'Failed');
                    $validator->errors()->add('routine', 'Duplicate subjects are not allowed in the exam routine.');
                }
            });

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    Toastr::error($error, 'Failed');
                }

                return redirect()->back()->withInput();
            }

            $class_id = $request->class_id;
            $section_id = $request->section_id;
            $exam_term_id = $request->exam_type_id;
            $shift_id = shiftEnable() ? ($request->shift_id == 0 ? 0 : $request->shift_id) : null;

            $exam_schedule = SmExamSchedule::query()
                ->when($request->class_id, function ($query, $class_id) {
                    return $query->where('class_id', $class_id);
                })
                ->when($request->section_id !== 0, function ($query, $section_id) {
                    return $query->where('section_id', $section_id);
                })
                ->when($request->shift_id !== 0, function ($query, $shift_id) {
                    return $query->where('shift_id', $shift_id);
                })
                ->where('exam_term_id', $request->exam_type_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->delete();

            foreach ($request->routine as $routine_data) {
                if (gv($routine_data, 'subject') == 'Select Subject *') {
                    Toastr::error('Subject Can not Be Empty', 'Failed');

                    $url = 'exam-routine-view/' . $class_id . '/' . $section_id . '/' . $exam_term_id;

                    if (shiftEnable()) {
                        $url .= '/' . $shift_id;
                    }

                    return redirect($url);
                }

                if (! gv($routine_data, 'subject')) {
                    continue;
                }

                if (gv($routine_data, 'subject') == 'Select Subject *') {
                    continue;
                }

                if (! gv($routine_data, 'start_time')) {
                    continue;
                }

                if (! gv($routine_data, 'end_time')) {
                    continue;
                }

                $is_exist = SmExamSchedule::where('exam_term_id', $request->exam_type_id)
                    ->where('subject_id', gv($routine_data, 'subject'))
                    ->where('date', date('Y-m-d', strtotime(gv($routine_data, 'date'))))
                    ->where('start_time', date('H:i:s', strtotime(gv($routine_data, 'start_time'))))
                    ->where('end_time', date('H:i:s', strtotime(gv($routine_data, 'end_time'))))
                    ->where('room_id', gv($routine_data, 'room'))
                    ->where('class_id', $request->class_id)
                    ->where('section_id', gv($routine_data, 'section'))
                    ->when(shiftEnable(), function ($query) use ($routine_data) {
                        $query->where('shift_id', gv($routine_data, 'shift'));
                    })
                    ->where('school_id', Auth::user()->school_id)
                    ->first();


                if ($is_exist) {
                    continue;
                }

                $exam_routine = new SmExamSchedule();
                $exam_routine->exam_term_id = $request->exam_type_id;
                $exam_routine->class_id = $request->class_id;
                $exam_routine->section_id = gv($routine_data, 'section');
                $exam_routine->shift_id = shiftEnable() ? gv($routine_data, 'shift') : '';
                $exam_routine->subject_id = gv($routine_data, 'subject');
                $exam_routine->teacher_id = gv($routine_data, 'teacher_id');
                $exam_routine->date = date('Y-m-d', strtotime(gv($routine_data, 'date')));
                $exam_routine->start_time = date('H:i:s', strtotime(gv($routine_data, 'start_time')));
                $exam_routine->end_time = date('H:i:s', strtotime(gv($routine_data, 'end_time')));
                $exam_routine->room_id = gv($routine_data, 'room');
                $exam_routine->school_id = auth()->user()->school_id;
                $exam_routine->academic_id = getAcademicId();
                $exam_routine->save();

                $data['class_id'] = $exam_routine->class_id;
                $data['section_id'] = $exam_routine->section_id;
                $data['subject'] = $exam_routine->subject->subject_name;
                $data['exam_schedule'] = $exam_routine->date.'('.$exam_routine->start_time.'-'.$exam_routine->end_time.')';
                $records = $this->studentRecordInfo($data['class_id'], $data['section_id'])->pluck('studentDetail.user_id');
                $this->sent_notifications('Exam_Schedule', $records, $data, ['Student', 'Parent']);
            }

            Toastr::success('Exam routine has been assigned successfully', 'Success');

            $url = 'exam-routine-view/' . $class_id . '/' . $section_id . '/' . $exam_term_id;

            if (shiftEnable()) {
                $url .= '/' . $shift_id;
            }

            return redirect($url);

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examRoutineView($class_id, $section_id, $exam_term_id, $shift_id = null)
    {

        /*
        try {
        */

            $subject_ids = SmExamSetup::query();

            if ($class_id !== null) {
                $subject_ids->where('class_id', $class_id);
            }

            if ($section_id !== 0) {
                $subject_ids->where('section_id', $section_id);
            }

            if (!shiftEnable() || $shift_id == 0) {
                $shift_id = null;
            }else{
                $shift_id = $shift_id;
            }

            $subject_ids = $subject_ids->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('exam_term_id', $exam_term_id)
                ->pluck('subject_id')->toArray();

            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $exams = SmExam::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $exam_type_id = $exam_term_id;

            $exam_types = SmExamType::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $exam_periods = SmClassTime::where('type', 'exam')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $rooms = SmClassRoom::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $subjects = SmSubject::whereIn('id', $subject_ids)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get(['id', 'subject_name']);

            $teachers = SmStaff::where('role_id', 4)->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get(['id', 'user_id', 'full_name']);

            $search_current_class = SmClass::find($class_id);
            $search_current_section = SmSection::find($section_id);

            if ($section_id == 0) {
                $exam_schedule = SmExamSchedule::where('class_id', $class_id)->where('exam_term_id', $exam_type_id)->get();
            } else {
                $exam_schedule = SmExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)
                    ->where('exam_term_id', $exam_type_id)->get();
            }

            $examName = SmExamType::where('id', $exam_type_id)->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->first()->title;

            return view('backEnd.examination.exam_schedule_new_update', ['classes' => $classes, 'subjects' => $subjects, 'exam_schedule' => $exam_schedule, 'exams' => $exams, 'class_id' => $class_id, 'shift_id' => $shift_id, 'section_id' => $section_id, 'exam_type_id' => $exam_type_id, 'exam_types' => $exam_types, 'teachers' => $teachers, 'rooms' => $rooms, 'examName' => $examName, 'search_current_class' => $search_current_class, 'search_current_section' => $search_current_section]);
        /*
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function universityExamRoutinePrint($un_semester_label_id, $un_section_id, $exam_term_id, $shift_id = null)
    {
        /*
        try {
        */
            $exam_type_id = $exam_term_id;
            $exam_type = SmExamType::find($exam_type_id)->title;
            $academic_id = SmExamType::find($exam_type_id)->academic_id;
            $academic_year = SmAcademicYear::find($academic_id);
            $class_name = $un_semester_label_id !== 0 ? UnSemesterLabel::find($un_semester_label_id)->name : 'All Classes';
            $section_name = $un_section_id !== 0 ? SmSection::find($un_section_id)->section_name : 'All Sections';
            $shift_name = (shiftEnable() && $shift_id != 0 && $shift_id !== null)
                            ? (Shift::find($shift_id)->name ?? 'Unknown Shift')
                            : 'All Shifts';
            $exam_schedules = SmExamSchedule::where('school_id', Auth::user()->school_id)
                            ->when($exam_term_id, function ($query) use ($exam_term_id) {
                                $query->where('exam_term_id', $exam_term_id);
                            })
                            ->when($un_semester_label_id != 0, function ($query) use ($un_semester_label_id) {
                                $query->where('un_semester_label_id', $un_semester_label_id);
                            })
                            ->when($un_section_id != 0, function ($query) use ($un_section_id) {
                                $query->where('un_section_id', $un_section_id);
                            })
                            ->when(shiftEnable() && $shift_id != 0 && $shift_id !== null, function ($query) use ($shift_id) {
                                $query->where('shift_id', $shift_id);
                            })
                            ->get();

            $print = request()->print;

            return view(
                'backEnd.examination.exam_schedule_print',
                [
                    'exam_schedules' => $exam_schedules,
                    'exam_type' => $exam_type,
                    'class_name' => $class_name,
                    'academic_year' => $academic_year,
                    'section_name' => $section_name,
                    'shift_name' => $shift_name,
                    'print' => $print,
                ]
            );

            $pdf = Pdf::loadView(
                'backEnd.examination.exam_schedule_print',
                [
                    'exam_schedules' => $exam_schedules,
                    'exam_type' => $exam_type,
                    'class_name' => $class_name,
                    'academic_year' => $academic_year,
                    'section_name' => $section_name,
                    'shift_name' => $shift_name,


                ]
            )->setPaper('A4', 'landscape');
            return $pdf->stream('EXAM_SCHEDULE.pdf');
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examRoutinePrint($class_id, $section_id, $exam_term_id, $shift_id = null)
    {
        if (moduleStatusCheck('University')) {
            return $this->universityExamRoutinePrint($class_id, $section_id, $exam_term_id, $shift_id);
        }

        /*
                try {
                */
        $exam_type_id   = $exam_term_id;
        $exam_type      = SmExamType::find($exam_type_id)->title;
        $academic_id    = SmExamType::find($exam_type_id)->academic_id;
        $academic_year  = SmAcademicYear::find($academic_id);
        $class_name     = $class_id != 0 ? SmClass::find($class_id)->class_name : 'All Classes';
        $section_name   = $section_id != 0 ? SmSection::find($section_id)->section_name : 'All Sections';
        $shift_name = (shiftEnable() && $shift_id != 0 && $shift_id != null)
                    ? (Shift::find($shift_id)->name ?? 'Unknown Shift')
                    : 'All Shifts';
        $exam_schedules = SmExamSchedule::where('school_id', Auth::user()->school_id)
            ->when($exam_term_id, function ($query) use ($exam_term_id) {
                $query->where('exam_term_id', $exam_term_id);
            })
            ->when($class_id != 0, function ($query) use ($class_id) {
                $query->where('class_id', $class_id);
            })
            ->when($section_id != 0, function ($query) use ($section_id) {
                $query->where('section_id', $section_id);
            })
            ->when($shift_id != 0, function ($query) use ($shift_id) {
                $query->where('shift_id', $shift_id);
            })
            ->get();

        // return view('backEnd.examination.exam_schedule_print', [
        //     'exam_schedules' => $exam_schedules,
        //     'exam_type' => $exam_type,
        //     'class_name' => $class_name,
        //     'academic_year' => $academic_year,
        //     'section_name' => $section_name,
        // ]);
        $print = request()->print;
        return view(
            'backEnd.examination.exam_schedule_prints',
            [
                'exam_schedules' => $exam_schedules,
                'exam_type' => $exam_type,
                'class_name' => $class_name,
                'academic_year' => $academic_year,
                'section_name' => $section_name,
                'shift_name' => $shift_name,
                'print' => $print,
            ]
        );

        $pdf = Pdf::loadView(
            'backEnd.examination.exam_schedule_prints',
            [
                'exam_schedules' => $exam_schedules,
                'exam_type' => $exam_type,
                'class_name' => $class_name,
                'academic_year' => $academic_year,
                'section_name' => $section_name,
                'shift_name' => $shift_name,


            ]
        )->setPaper('A4', 'landscape');
        return $pdf->stream('EXAM_SCHEDULE.pdf');
        /*
    } catch (\Exception $e) {
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }
    */

    }

    public function deleteExamRoutine(Request $request)
    {

        /*
        try {
        */

            $exam_routine = SmExamSchedule::find($request->id);
            $result = $exam_routine->delete();

            return response(['done']);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
