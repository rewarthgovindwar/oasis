<?php

namespace App\Http\Controllers\Admin\Academics;

use Exception;
use App\SmClass;
use App\SmStaff;
use App\SmSubject;
use App\YearCheck;
use App\ApiBaseMethod;
use App\SmClassSection;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use App\Traits\NotificationSend;
use App\Events\CreateClassGroupChat;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Modules\University\Entities\UnSubject;

class SmAssignSubjectController extends Controller
{
    use NotificationSend;

    public function index(Request $request)
    {

        /*
        try {
        */
        $classes = SmClass::get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($classes, null);
        }
        return view('backEnd.academics.assign_subject', ['classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function create(Request $request)
    {
        /*
        try {
        */
        $classes = SmClass::get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($classes, null);
        }

        return view('backEnd.academics.assign_subject_create', ['classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function ajaxSubjectDropdown(Request $request)
    {
        /*
        try {
        */
        $staff_info = SmStaff::where('user_id', Auth::user()->id)->first();
        if (teacherAccess()) {
            $class_id = $request->class;
            $allSubjects = SmAssignSubject::where([['section_id', '=', $request->id], ['class_id', $class_id], ['teacher_id', $staff_info->id]])->where('school_id', Auth::user()->school_id)->get();
            $subjectsName = [];
            foreach ($allSubjects as $allSubject) {
                $subjectsName[] = SmSubject::find($allSubject->subject_id);
            }
        } else {
            $class_id = $request->class;
            $allSubjects = SmAssignSubject::where([['section_id', '=', $request->id], ['class_id', $class_id]])->where('school_id', Auth::user()->school_id)->get();

            $subjectsName = [];
            foreach ($allSubjects as $allSubject) {
                $subjectsName[] = SmSubject::find($allSubject->subject_id);
            }
        }

        return response()->json([$subjectsName]);
        /*
        } catch (Exception $exception) {
            return Response::json(['error' => 'Error msg'], 404);
        }
        */
    }

    public function search(Request $request)
    {
        $input = $request->all();
        
        if(moduleStatusCheck('University')){
            $validator = Validator::make($input, [
                'un_session_id' => 'required',
                'un_faculty_id' => 'required',
                'un_department_id' => 'required',
                'un_academic_id' => 'required',
                'un_semester_id' => 'required',
                'un_semester_label_id' => 'required',
                'un_section_id' => 'required',
            ]);
        }else{
            $validator = Validator::make($input, [
                'class' => 'required',
                'section' => 'required',
            ]);
        }
       

        if ($validator->fails()) {

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        /*
        try {
        */

        if(!moduleStatusCheck('University')){
            $sections = SmClassSection::where('class_id', $request->class)
                ->with('sectionName', 'className')
                ->when($request->section, function ($q) use ($request): void {
                    $q->where('section_id', $request->section);
                })->get();

            $assign_subjects = SmAssignSubject::where('class_id', $request->class)
                ->when($request->section, function ($q) use ($request): void {
                    $q->where('section_id', $request->section);
                });

            if (shiftEnable()) {
                $assign_subjects = $assign_subjects->when($request->shift, function ($q) use ($request) {
                    $q->where('shift_id', $request->shift);
                });
            }

            $assign_subjects = $assign_subjects->get();

            $subjects = SmSubject::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $teachers = SmStaff::where('active_status', 1)
                ->where(function ($q): void {
                    $q->where('role_id', 4)->orWhere('previous_role_id', 4);
                })
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $class_id = $request->class;
            $section_id = $request->section;
            $shift_id = shiftEnable() ? $request->shift : null;

            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            return view('backEnd.academics.assign_subject_create', [
                'classes' => $classes,
                'sections' => $sections,
                'assign_subjects' => $assign_subjects,
                'teachers' => $teachers,
                'subjects' => $subjects,
                'class_id' => $class_id,
                'section_id' => $section_id,
                'shift_id' => $shift_id,
            ]);
        }else{
            $teachers = SmStaff::where('active_status', 1)
            ->where(function ($q): void {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->where('school_id', Auth::user()->school_id)->get();

            $subjects = UnSubject::where('un_department_id',$input['un_department_id'])
                                        ->where('un_faculty_id',$input['un_faculty_id'])
                                        ->where('school_id',Auth::user()->school_id)
                                        ->get();
            $assign_subjects = SmAssignSubject::where('un_faculty_id', $request->un_department_id)
                                                ->where('un_department_id', $request->un_department_id)
                                                ->where('un_section_id', $request->un_section_id)
                                                ->where('un_session_id', $request->un_session_id)
                                                ->where('un_semester_label_id', $request->un_semester_label_id)
                                                ->where('un_academic_id', $request->un_academic_id)
                                                ->where('school_id',Auth::user()->school_id)
                                                ->get();
            return view('backEnd.academics.assign_subject_create', ['assign_subjects' => $assign_subjects, 'teachers' => $teachers,'subjects' => $subjects,'un_input' => $input]);
        }
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function assignSubjectAjax(Request $request)
    {

        /*
        try {
        */
        $subjects = SmSubject::get();
        $teachers = SmStaff::status()->where(function ($q): void {
            $q->where('role_id', 4)->orWhere('previous_role_id', 4);
        })->get();

        return response()->json([$subjects, $teachers]);
        /*
        } catch (Exception $exception) {
            return Response::json(['error' => 'Error msg'], 404);
        }
        */
    }

    public function assignSubjectStore(Request $request)
    {
        //try{
            $user = Auth::user();
        if ($request->subjects && $request->teachers && is_null($request->subjects[0]) && is_null($request->teachers[0])) {
            Toastr::warning('Empty data submit', 'warning');
            return redirect()->back();
        }
        if ($request->update == 0) {
            $i = 0;
            if ($request->subjects) {
                foreach ($request->subjects as $key => $subject) {
                    if ($subject) {
                        if(moduleStatusCheck('University')){
                            $assign_subject = new SmAssignSubject();                            
                            $assign_subject->school_id = $user->school_id;
                            $assign_subject->un_faculty_id = $request->un_faculty_id;
                            $assign_subject->un_department_id = $request->un_department_id;
                            $assign_subject->un_section_id = $request->un_section_id;
                            $assign_subject->un_session_id = $request->un_session_id;
                            $assign_subject->un_semester_label_id = $request->un_semester_label_id;
                            $assign_subject->un_academic_id = $request->un_academic_id;
                            $assign_subject->un_subject_id = $subject;
                            $assign_subject->teacher_id = $request->teachers[$i];
                            $assign_subject->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                            $assign_subject->academic_id = getAcademicId();
                            $assign_subject->save();
                            $i++;
                        }else{
                            if (!$request->section_id) {
                                $all_section = SmClassSection::where('class_id', $request->class)->get();
                                foreach ($all_section as $section) {
                                    $assign_subject = new SmAssignSubject();
                                    $assign_subject->class_id = $request->class;
                                    $assign_subject->school_id = $user->school_id;
                                    $assign_subject->section_id = $section->section_id;
                                    $assign_subject->shift_id = shiftEnable() ? $request->shift : null;
                                    $assign_subject->subject_id = $subject;
                                    $assign_subject->teacher_id = $request->teachers[$key];
                                    $assign_subject->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                                    $assign_subject->academic_id = getAcademicId();
                                    $assign_subject->save();
                                    event(new CreateClassGroupChat($assign_subject));
                                }
                            } else {
                                $assign_subject = new SmAssignSubject();
                                $assign_subject->class_id = $request->class;
                                $assign_subject->school_id = $user->school_id;
                                $assign_subject->section_id = $request->section_id;
                                $assign_subject->shift_id = shiftEnable() ? $request->shift : null;
                                $assign_subject->subject_id = $subject;
                                $assign_subject->teacher_id = $request->teachers[$i];
                                $assign_subject->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                                $assign_subject->academic_id = getAcademicId();
                                $assign_subject->save();
                                event(new CreateClassGroupChat($assign_subject));
                                $i++;
                            }

                        }
                        
                    }
                }
            }
        } elseif ($request->update == 1) {
            if(moduleStatusCheck('University')){                
                    $i = 0;                    
                    if ($request->subjects) {
                        foreach ($request->subjects as $key => $subject) {
                            SmAssignSubject::where('un_faculty_id', $request->un_faculty_id)
                                ->where('un_department_id', $request->un_department_id)
                                ->where('un_section_id', $request->un_section_id)
                                ->where('un_session_id', $request->un_session_id)
                                ->where('un_semester_label_id', $request->un_semester_label_id)
                                ->where('un_academic_id', $request->un_academic_id)
                                ->where('un_subject_id',$subject)
                                ->delete();
                            if ($subject) {
                                $assign_subject = new SmAssignSubject();                            
                                $assign_subject->school_id = $user->school_id;
                                $assign_subject->un_faculty_id = $request->un_faculty_id;
                                $assign_subject->un_department_id = $request->un_department_id;
                                $assign_subject->un_section_id = $request->un_section_id;
                                $assign_subject->un_session_id = $request->un_session_id;
                                $assign_subject->un_semester_label_id = $request->un_semester_label_id;
                                $assign_subject->un_academic_id = $request->un_academic_id;
                                $assign_subject->un_subject_id = $subject;
                                $assign_subject->teacher_id = $request->teachers[$i];
                                $assign_subject->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                                $assign_subject->academic_id = getAcademicId();
                                $assign_subject->save();
                            }
                        }
                    }
            }else{
                if (!$request->section_id) {
                    if ($request->subjects) {
    
                        foreach ($request->subjects as $key => $subject) {
                            if ($subject) {
                                $all_section = SmClassSection::where('class_id', $request->class)->get();
                                foreach ($all_section as $section) {
                                    $assign_subject = new SmAssignSubject();
                                    $assign_subject->class_id = $request->class;
                                    $assign_subject->section_id = $section->section_id;
                                    $assign_subject->shift_id = shiftEnable() ? $request->shift : null;
                                    $assign_subject->subject_id = $subject;
                                    $assign_subject->teacher_id = $request->teachers[$key];
                                    $assign_subject->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                                    $assign_subject->academic_id = getAcademicId();
                                    $assign_subject->school_id = $user->school_id;
    
                                    $assign_subject->save();
                                    event(new CreateClassGroupChat($assign_subject));
                                }
                            }
                        }
                    }
                } else {
                    SmAssignSubject::where('class_id', $request->class)->where('section_id', $request->section_id)->delete();
                    $i = 0;
                    if ($request->subjects) {
                        foreach ($request->subjects as $subject) {
                            if ($subject) {
                                $assign_subject = new SmAssignSubject();
                                $assign_subject->class_id = $request->class;
                                $assign_subject->section_id = $request->section_id;
                                $assign_subject->shift_id = shiftEnable() ? $request->shift : null;
                                $assign_subject->subject_id = $subject;
                                $assign_subject->teacher_id = $request->teachers[$i];
                                $assign_subject->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                                $assign_subject->academic_id = getAcademicId();
                                $assign_subject->school_id = $user->school_id;
                                $assign_subject->save();
                                event(new CreateClassGroupChat($assign_subject));
                                $i++;
                            }
                        }
                    }
                }

            }
           
        }
        Toastr::success('Operation successful', 'Success');
        return redirect()->back();
        // }catch(Exception $e){
        //     dd($e);
        // }     
    }

    public function assignSubjectFind(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $classes = SmClass::get();
        $assign_subjects = SmAssignSubject::where('class_id', $request->class)
            ->where('section_id', $request->section);
        if (shiftEnable()) {
            $assign_subjects = $assign_subjects->where('shift_id', $request->shift);
        }
        $assign_subjects = $assign_subjects->get();
        $subjects = SmSubject::get();
        $teachers = SmStaff::status()->where(function ($q): void {
            $q->where('role_id', 4)->orWhere('previous_role_id', 4);
        })->get();
        
        if ($assign_subjects->count() == 0) {
            Toastr::error('No Result Found', 'Failed');
            return redirect()->back();
        }

        $class_id = $request->class;
        $section_id = $request->section;
        $shift_id = $request->shift;

        return view('backEnd.academics.assign_subject', ['classes' => $classes,'assign_subjects' =>  $assign_subjects,'teachers' => $teachers, 'subjects' => $subjects, 'class_id' => $class_id, 'section_id' => $section_id, 'shift_id' => $shift_id ]);
    }


    public function ajaxSelectSubject(Request $request)
    {

        $subject_all = SmAssignSubject::where('class_id', '=', $request->class)->where('section_id', $request->section)->distinct('subject_id')->where('school_id', Auth::user()->school_id)->get();
            foreach ($subject_all as $allSubject) {
                $students[] = SmSubject::find($allSubject->subject_id);
            }
            return response()->json([$students]);
    }
}
