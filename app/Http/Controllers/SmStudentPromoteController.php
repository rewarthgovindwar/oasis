<?php

namespace App\Http\Controllers;

use App\SmClass;
use App\SmSection;
use App\SmStudent;
use App\SmExamType;
use App\Models\Shift;
use App\SmAcademicYear;
use App\SmClassSection;
use App\Models\Graduate; 
use App\SmStudentPromotion;
use App\CustomResultSetting;
use Illuminate\Http\Request; 
use App\Models\StudentRecord;
use App\SmAssignClassTeacher;
use App\SmTemporaryMeritlist;
use App\Traits\NotificationSend;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Admin\StudentInfo\SmStudentAdmissionController;

class SmStudentPromoteController extends Controller
{
    //
    use NotificationSend;

    public function index()
    {
        try {

            if (moduleStatusCheck('University')) {
                return redirect()->route('university.student_promote');
            }

            $generalSetting = generalSetting();
            $sessions = SmAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->get();
            $exams = SmExamType::where('active_status', 1)->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->get();

            if ($generalSetting->promotionSetting == 0) {
                return view('backEnd.studentInformation.student_promote_new', compact('sessions', 'classes'));
            } else {
                return view('backEnd.studentInformation.student_promote_with_exam', compact('sessions', 'classes', 'exams'));
            }
        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentCurrentSearch(Request $request)
    {
        
        
        $request->validate([
            'current_session' => 'required',
            'promote_session' => 'required',
            'current_class' => 'required',
            'current_section' => 'required',
        ]);

        $current_section = [];
        try {
            $auth = Auth::user();
            $student_ids = StudentRecord::when($request->current_session, function ($query) use ($request) {
                $query->where('academic_id', $request->current_session);
            })
                ->when($request->current_class, function ($query) use ($request) {
                    $query->where('class_id', $request->current_class);
                })
                ->when($request->current_section, function ($query) use ($request) {
                    $query->where('section_id', $request->current_section);
                })
                ->when($request->shift, function ($query) use ($request) {
                    $query->where('shift_id', $request->shift);
                })
                ->where('is_promote', 0)->where('school_id', $auth->school_id)
                ->pluck('student_id')->unique();

            $students = SmStudent::query()->with('class', 'section','studentRecords');
            $students = $students->whereIn('id', $student_ids)->where('active_status', 1)
                ->where('school_id', $auth->school_id)
                ->withOutGlobalScope(StatusAcademicSchoolScope::class)
                ->get();

            $current_session = $request->current_session;
            $class_id = $request->current_class;
            $section_id = $request->current_section;
            $shift_id = shiftEnable() ? $request->shift : null;
            $promote_session = $request->promote_session;
            $sessions = SmAcademicYear::where('active_status', 1)
                ->where('school_id', $auth->school_id)
                ->get();
            $currrent_academic_class = SmClass::where('active_status', 1)
                ->where('academic_id', $request->current_session)
                //->withOutGlobalScope(StatusAcademicSchoolScope::class)
                ->where('school_id', $auth->school_id)
                ->select(['id','class_name','active_status'])
                ->get();

            $classes = SmClass::with(['classSection' => function($query){ $query->select(['section_id','class_id','id']); }])->where('active_status', 1)
                ->where('academic_id', $request->promote_session)
                //->withOutGlobalScope(StatusAcademicSchoolScope::class)
                ->where('school_id', $auth->school_id)
                ->select(['id','class_name','active_status'])->get();
                
            
            if (empty($classes)) {
                Toastr::error('No Class found For Next Academic Year', 'Failed');
                return redirect('student-promote');
            }

            $next_class = $classes->except($class_id)->first();

            $next_sections = collect();
            if ($next_class) {
                $next_sections = SmClassSection::with('sectionWithoutGlobal')->where('class_id', '=', $next_class->id)->where('academic_id', $request->promote_session)
                    ->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            }

            $search_current_class = SmClass::withoutGlobalScope(StatusAcademicSchoolScope::class)->findOrFail($request->current_class);
            $search_current_section = SmSection::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($request->current_section);
            if(shiftEnable()){
                $search_current_shift = Shift::find($request->shift);
            }else{
                $search_current_shift = null;
            }
            $search_current_academic_year = SmAcademicYear::find($request->current_session);
            $search_promote_academic_year = SmAcademicYear::find($request->promote_session);
            $sections = $search_current_class ? $search_current_class->classSection : [];
            if (empty($students)) {
                Toastr::error('No result found', 'Failed');
                return redirect('student-promote');
            }

            return view('backEnd.studentInformation.student_promote_new', compact('currrent_academic_class', 'next_class', 'sessions', 'classes', 'students', 'current_session', 'class_id', 'shift_id', 'section_id', 'current_section', 'promote_session', 'search_current_class', 'search_current_section', 'search_current_academic_year', 'search_promote_academic_year', 'search_current_shift', 'sections', 'next_sections'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function rollCheck(Request $request)
    {

        $exist_roll_number = SmStudent::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('roll_no', $request->promote_roll_number)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->count();

        return response()->json($exist_roll_number);
    }

    public function promote(Request $request)
    {
        $isGraduateSelected = isset($request->is_graduate) && $request->is_graduate == 1;
        
        $validationRules = [
            'promote_session' => $isGraduateSelected ? 'nullable' : 'required',
        ];

        $request->validate($validationRules);

        if(!empty($request->promote) && count($request->promote) > 0)
        {
            foreach ($request->promote as $key => $promote) {
                $validationRules["promote.$key.class"] = $isGraduateSelected || empty($promote['class']) ? 'nullable' : 'required';
                $validationRules["promote.$key.section"] = $isGraduateSelected || empty($promote['section']) ? 'nullable' : 'required';
                $validationRules["promote.$key.roll_number"] = $isGraduateSelected || empty($promote['roll_number']) ? 'nullable' : 'required';
            }
            try {
                foreach ($request->promote as $student_id => $student_data) {
                    if ( gv($student_data, 'student') && gv($student_data, 'class') && gv($student_data, 'section')) {
                        $roll_number = gv($student_data, 'roll_number');
                        $student_record = StudentRecord::where('student_id', $student_id)
                            ->where('class_id', gv($student_data, 'class'))
                            ->where('section_id', gv($student_data, 'section'))
                            ->when(shiftEnable(), function ($q) use ($student_data) {
                                $q->where('shift_id', gv($student_data, 'shift'));
                            })
                            ->where('academic_id', $request->promote_session)
                            ->where('school_id', Auth::user()->school_id)
                            ->where('is_promote', 0)
                            ->first();
                
                        $exit_record = $student_record;
                        if ($roll_number) {
                            $exist_roll_number = $exit_record;
                            if ($exist_roll_number) {
                                throw ValidationException::withMessages(['promote.' . $student_id . '.roll_number' => 'Roll no already exist']);
                            }
                        } else {
                            $roll_number = StudentRecord::where('class_id', (int)gv($student_data, 'class'))
                                        ->where('section_id', (int)gv($student_data, 'section'))
                                        ->when(shiftEnable(), function ($q) use ($student_data) {
                                            $q->where('shift_id', (int)gv($student_data, 'shift'));
                                        })
                                        ->where('academic_id', $request->promote_session)
                                        ->where('school_id', Auth::user()->school_id)
                                        ->max('roll_no') + 1;
                        }
                
                        $current_student = SmStudent::where('id', $student_id)->first();
                        $pre_record = StudentRecord::where('student_id', $student_id)
                            ->where('class_id', $request->pre_class)
                            ->where('section_id', $request->pre_section)
                            ->when(shiftEnable(), function ($q) use ($request) {
                                $q->where('shift_id', $request->pre_shift);
                            })
                            ->where('academic_id', $request->current_session)
                            ->where('school_id', Auth::user()->school_id)
                            ->first();
                
                            if (!$exit_record) {
                                $student_promote = new SmStudentPromotion();
                                $student_promote->student_id = $student_id;
    
                                $student_promote->previous_class_id = $request->pre_class;
                                $student_promote->current_class_id = gv($student_data, 'class');
    
                                $student_promote->previous_session_id = $request->current_session;
                                $student_promote->current_session_id = $request->promote_session;
    
                                $student_promote->previous_section_id = $request->pre_section;
                                $student_promote->current_section_id = gv($student_data, 'section');
                                
                                $student_promote->previous_shift_id = $request->pre_shift;
                                $student_promote->current_shift_id = gv($student_data, 'shift');

                                $student_promote->admission_number = $current_student->admission_no;
                                $student_promote->student_info = $current_student->toJson();
                                $student_promote->merit_student_info = $current_student->toJson();
                                $student_promote->previous_roll_number = $pre_record->roll_no;
                                $student_promote->current_roll_number = $roll_number;
                                $student_promote->academic_id = $request->promote_session;
                                $student_promote->result_status = gv($student_data, 'result') ? gv($student_data, 'result') : 'F';
                                $student_promote->save();
    
                                $teacherInfo = SmAssignClassTeacher::where('class_id', $student_promote->current_class_id)
                                ->where('shift_id', $student_promote->current_shift_id)
                                ->when(shiftEnable() && !empty($student_promote->current_shift_id),function($query) use($student_promote){
                                    $query->were('shift_id',$student_promote->current_shift_id);
                                })
                                ->where('section_id', $student_promote->current_section_id)->first();
                                $data['class'] = gv($student_data, 'class');
                                $data['section'] = gv($student_data, 'section');
                                $data['shift'] = gv($student_data, 'shift');
                                if (!is_null($teacherInfo)) {
                                    foreach ($teacherInfo->classTeachers as $teacher) {
                                        $data['teacher_name'] = $teacher->teacher->full_name;
                                        $this->sent_notifications('Student_Promote', [$teacher->teacher->user_id], $data, ['Teacher']);
                                    }
                                };
                                $this->sent_notifications('Student_Promote', [$current_student->user_id], $data, ['Student', 'Parent']);
    
    
                                $insertStudentRecord = new SmStudentAdmissionController;
                                $result = $insertStudentRecord->insertStudentRecord($request->merge([
                                    'student_id' => gv($student_data, 'student'),
                                    'roll_number' => $roll_number,
                                    'class' => gv($student_data, 'class'),
                                    'section' => gv($student_data, 'section'),
                                    'shift' => gv($student_data, 'shift'),
                                    'session' => $request->promote_session,
                                ]));
    
                                $groups = \Modules\Chat\Entities\Group::where([
                                        'class_id' => $request->pre_class,
                                        'section_id' => $request->pre_section,
                                        'academic_id' => $request->current_session,
                                        'school_id' => auth()->user()->school_id
                                    ])
                                    ->when(shiftEnable(), function ($q) use ($request) {
                                        $q->where('shift_id', $request->pre_shift);
                                    })
                                    ->get();
    
                                if ($current_student) {
                                    $user = $current_student->user;
                                    foreach ($groups as $group) {
                                        removeGroupUser($group, $user->id);
                                    }
                                }
                                $pre_record->is_promote = 1;
                                $pre_record->save();
                            }
                
                        $compact['user_email'] = $pre_record->studentDetail->email;
                        @send_sms($pre_record->studentDetail->mobile, 'student_promote', $compact);
                    }
    
                    if (gv($student_data, 'student') && is_null(gv($student_data, 'section'))) {
                        $student_record = StudentRecord::where('student_id', $student_id)
                            ->where('school_id', Auth::user()->school_id)
                            ->where('is_promote', 0)
                            ->first();
    
                        if ($student_record) {
                            $student_record->is_graduate = 1;
                            $student_record->is_promote = 1;
                            $student_record->save();
    
                            $graduate = new Graduate();
                            $graduate->student_id = $student_record->student_id;
                            $graduate->record_id = $student_record->id;
    
                            $graduate->school_id = Auth::user()->school_id;
                            $graduate->created_by = Auth::id();
                            $graduate->created_at = now();
                            if (moduleStatusCheck('University')) {
                                $graduate->un_session_id = $student_record->session_id;
                                $graduate->un_faculty_id = $student_record->un_faculty_id;
                                $graduate->un_department_id = $student_record->un_department_id;
                            } else {
                                $graduate->class_id = $student_record->class_id;
                                $graduate->section_id = $student_record->section_id;
                                $graduate->session_id = $student_record->session_id;
                                if(shiftEnable()){
                                    $graduate->shift_id = $student_record->shift_id;
                                }
                            }
                            $graduate->save();
                        } else {
                            Toastr::error('Student Not Found', 'Failed');
                            return back();
                        }
                    }
                }
    
                Toastr::success('Operation Successful', 'Success');
                return back();
            } catch (\Exception $e) {
                Toastr::error($e->getMessage(), 'Failed');
                return redirect()->back();
            }
        }else{
            Toastr::error('Promoted student not found', 'Failed');
            return redirect()->back();
        }

    }


    public function studentSearchWithExam(Request $request)
    {
        $request->validate([
            'current_session' => 'required',
            'promote_session' => 'required',
            'current_class' => 'required',
            'current_section' => 'sometimes',
            'exam' => 'required',
        ]);

        try {

            $meritListSettings = CustomResultSetting::first('merit_list_setting')->merit_list_setting;

            // $merit_list = $this->meritList($request);

            $student_ids = StudentRecord::query()->with('class', 'section');

            if ($request->current_session) {
                $student_ids->where('academic_id', $request->current_session);
            }
            if ($request->current_class) {
                $student_ids->where('class_id', '=', $request->current_class);
            }
            if ($request->current_section) {
                $student_ids->where('section_id', $request->current_section);
            }
            if ($request->shift) {
                $student_ids->where('shift_id', $request->shift);
            }
            $student_ids = $student_ids->where('is_promote', 0)
                ->orderBy('roll_no', 'ASC')
                ->where('school_id', Auth::user()->school_id)
                ->get()->pluck('student_id')->toArray();

            $students = SmTemporaryMeritlist::query()->with('class', 'studentinfo', 'section');


            if ($request->current_session) {
                $students->where('academic_id', $request->current_session);
            }
            if ($request->current_class) {
                $students->where('class_id', $request->current_class);
            }
            if ($request->current_section) {
                $students->Where('section_id', $request->current_section);
            }
            if ($request->shift) {
                $students->Where('shift_id', $request->shift);
            }
            if ($meritListSettings == "total_grade") {
                $students->orderBy('gpa_point', 'DESC');
            } else {
                $students->orderBy('total_marks', 'DESC');
            }
            $students = $students->whereIn('student_id', $student_ids)
                ->where('school_id', Auth::user()->school_id)
                ->get();


            if (count($students) == 0) {
                Toastr::error('Please Check Your Merit List First', 'Failed');
                return redirect('student-promote');
            }

            $current_session = $request->current_session;
            $current_class = $request->current_class;
            $current_section = $request->current_section;
            $current_shift = $request->shift;
            $promote_session = $request->promote_session;
            $exam_id = $request->exam;
            $sessions = SmAcademicYear::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $classes = SmClass::with('classSection')->where('active_status', 1)
                ->where('academic_id', $request->promote_session)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            // return $classes;
            if (empty($classes)) {
                Toastr::error('No Class found For Next Academic Year', 'Failed');
                return redirect('student-promote');
            }

            $next_class = $classes->except($current_class)->first();
            $search_current_class = SmClass::findOrFail($request->current_class);
            $search_current_section = SmSection::find($request->current_section);
            $search_current_academic_year = SmAcademicYear::find($request->current_session);
            $search_promote_academic_year = SmAcademicYear::find($request->promote_session);
            $search_current_shift = Shift::find($request->shift);
            if(shiftEnable())
            {
                $search_current_shift = $search_current_shift;
            }else{
                $search_current_shift = '';
            }
            $search_exams = SmExamType::find($request->exam)->title;
            $sections = $search_current_class ? $search_current_class->classSection : [];
            $exams = SmExamType::where('active_status', 1)->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->get();

            // return $search_info;
            if (empty($students)) {
                Toastr::error('No result found', 'Failed');
                return redirect('student-promote');
            }
            return view('backEnd.studentInformation.student_promote_with_exam', compact('next_class', 'sessions', 'classes', 'students', 'current_session', 'current_class', 'current_section', 'promote_session', 'search_current_class', 'search_current_section', 'search_current_academic_year', 'search_promote_academic_year', 'sections', 'exams', 'exam_id', 'search_exams', 'current_shift', 'search_current_shift'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
