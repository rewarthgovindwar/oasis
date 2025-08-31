<?php

namespace App\Http\Controllers\Admin\Academics;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmAssignClassTeacher;
use App\SmAssignSubject;
use App\SmClass;
use App\SmClassRoutine;
use App\SmFeesAssign;
use App\SmFeesMaster;
use App\SmFeesPayment;
use App\SmSection;
use App\SmStudent;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AcademicController extends Controller
{

    public function classRoutine()
    {
        $classes = SmClass::get();
        return view('backEnd.academics.class_routine', compact('classes'));
        /*
        try {
            $classes = SmClass::get();

            return view('backEnd.academics.class_routine', ['classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function classRoutineCreate()
    {
        $classes = SmClass::get();
        return view('backEnd.academics.class_routine_create', compact('classes'));
        /*
        try {
            $classes = SmClass::get();

            return view('backEnd.academics.class_routine_create', ['classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function assignRoutineSearch(Request $request)
    {
        $request->validate([
            'class' => 'required',
            'section' => 'required',
            'subject' => 'required',
        ]);

        $class_id = $request->class;
        $section_id = $request->section;
        $subject_id = $request->subject;
        $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
        $class_routine = SmClassRoutine::where('class_id', $request->class)->where('section_id', $request->section)->where('subject_id', $request->subject)->first();
        if ($class_routine == "") {
            $class_routine = "hello";
        }

        return view('backEnd.academics.class_routine_create', compact('class_routine', 'class_id', 'section_id', 'subject_id', 'classes'));

        /*
        try {
            $class_id = $request->class;
            $section_id = $request->section;
            $subject_id = $request->subject;
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_routine = SmClassRoutine::where('class_id', $request->class)->where('section_id', $request->section)->where('subject_id', $request->subject)->first();
            if ($class_routine == '') {
                $class_routine = 'hello';
            }

            return view('backEnd.academics.class_routine_create', ['class_routine' => $class_routine, 'class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function assignRoutineStore(Request $request)
    {
        $class_routine = SmClassRoutine::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->delete();
        // if($check_assigned != ""){
        $class_routine = new SmClassRoutine();
        $class_routine->class_id = $request->class_id;
        $class_routine->section_id = $request->section_id;
        $class_routine->subject_id = $request->subject_id;

        $class_routine->monday_start_from = $request->monday_start_from;
        $class_routine->monday_end_to = $request->monday_end_to;
        $class_routine->monday_room_id = $request->monday_room;

        $class_routine->tuesday_start_from = $request->tuesday_start_from;
        $class_routine->tuesday_end_to = $request->tuesday_end_to;
        $class_routine->tuesday_room_id = $request->tuesday_room;

        $class_routine->wednesday_start_from = $request->wednesday_start_from;
        $class_routine->wednesday_end_to = $request->wednesday_end_to;
        $class_routine->wednesday_room_id = $request->wednesday_room;

        $class_routine->thursday_start_from = $request->thursday_start_from;
        $class_routine->thursday_end_to = $request->thursday_end_to;
        $class_routine->thursday_room_id = $request->thursday_room;

        $class_routine->friday_start_from = $request->friday_start_from;
        $class_routine->friday_end_to = $request->friday_end_to;
        $class_routine->friday_room_id = $request->friday_room;

        $class_routine->saturday_start_from = $request->saturday_start_from;
        $class_routine->saturday_end_to = $request->saturday_end_to;
        $class_routine->saturday_room_id = $request->saturday_room;

        $class_routine->sunday_start_from = $request->sunday_start_from;
        $class_routine->sunday_end_to = $request->sunday_end_to;
        $class_routine->sunday_room_id = $request->sunday_room;

        $class_routine->academic_id = getAcademicId();
        $class_routine->save();
        // }else{

        // }
        Toastr::success('Operation successful', 'Success');
        return redirect('class-routine');

        /*
        try {
            $class_routine = SmClassRoutine::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('subject_id', $request->subject_id)
                ->delete();
            // if($check_assigned != ""){
            $class_routine = new SmClassRoutine();
            $class_routine->class_id = $request->class_id;
            $class_routine->section_id = $request->section_id;
            $class_routine->subject_id = $request->subject_id;

            $class_routine->monday_start_from = $request->monday_start_from;
            $class_routine->monday_end_to = $request->monday_end_to;
            $class_routine->monday_room_id = $request->monday_room;

            $class_routine->tuesday_start_from = $request->tuesday_start_from;
            $class_routine->tuesday_end_to = $request->tuesday_end_to;
            $class_routine->tuesday_room_id = $request->tuesday_room;

            $class_routine->wednesday_start_from = $request->wednesday_start_from;
            $class_routine->wednesday_end_to = $request->wednesday_end_to;
            $class_routine->wednesday_room_id = $request->wednesday_room;

            $class_routine->thursday_start_from = $request->thursday_start_from;
            $class_routine->thursday_end_to = $request->thursday_end_to;
            $class_routine->thursday_room_id = $request->thursday_room;

            $class_routine->friday_start_from = $request->friday_start_from;
            $class_routine->friday_end_to = $request->friday_end_to;
            $class_routine->friday_room_id = $request->friday_room;

            $class_routine->saturday_start_from = $request->saturday_start_from;
            $class_routine->saturday_end_to = $request->saturday_end_to;
            $class_routine->saturday_room_id = $request->saturday_room;

            $class_routine->sunday_start_from = $request->sunday_start_from;
            $class_routine->sunday_end_to = $request->sunday_end_to;
            $class_routine->sunday_room_id = $request->sunday_room;

            $class_routine->academic_id = getAcademicId();
            $class_routine->save();
            // }else{

            // }
            Toastr::success('Operation successful', 'Success');

            return redirect('class-routine');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function classRoutineReportSearch(Request $request)
    {
        $request->validate([
            'class' => 'required',
            'section' => 'required',
        ]);

        $classes = SmClass::get();

        $class_routines = SmClassRoutine::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', Auth::user()->school_id)->get();
        $class_id = $request->class;
        return view('backEnd.academics.class_routine', compact('class_routines', 'classes', 'class_id'));

        /*
        try {

            $classes = SmClass::get();

            $class_routines = SmClassRoutine::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', Auth::user()->school_id)->get();
            $class_id = $request->class;

            return view('backEnd.academics.class_routine', ['class_routines' => $class_routines, 'classes' => $classes, 'class_id' => $class_id]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function classReport(Request $request)
    {
        $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($classes, null);
        }
        return view('backEnd.reports.class_report', compact('classes'));
        /*
        try {

            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }

            return view('backEnd.reports.class_report', ['classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function classReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $class = SmClass::where('id', $request->class)->first();

        if ($request->section != "") {
            $section = SmSection::where('id', $request->section)->first();
        } else {
            $section = '';
        }

        $students = SmStudent::query();
        $students->where('active_status', 1);
        if ($request->section != "") {
            $students->where('section_id', $request->section);
        }
        $students->where('class_id', $request->class);
        $students = $students->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();


        $assign_subjects = SmAssignSubject::query();
        $assign_subjects->where('active_status', 1);
        if ($request->section != "") {
            $assign_subjects->where('section_id', $request->section);
        }
        $assign_subjects->where('class_id', $request->class);
        $assign_subjects = $assign_subjects->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();


        $assign_class_teacher = SmAssignClassTeacher::query();
        $assign_class_teacher->where('active_status', 1);
        if ($request->section != "") {
            $assign_class_teacher->where('section_id', $request->section);
        }
        $assign_class_teacher->where('class_id', $request->class);
        $assign_class_teacher = $assign_class_teacher->first();
        if ($assign_class_teacher != "") {
            $assign_class_teachers = $assign_class_teacher->classTeachers->first();
        } else {
            $assign_class_teachers = '';
        }


        $total_collection = 0;
        $total_assign = 0;
        foreach ($students as $student) {
            $fees_assigns = SmFeesAssign::where("student_id", $student->id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            foreach ($fees_assigns as $fees_assign) {
                $fees_masters = SmFeesMaster::where('id', $fees_assign->fees_master_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                foreach ($fees_masters as $fees_master) {
                    $total_collection = $total_collection + SmFeesPayment::where('active_status',1)->where('student_id', $student->id)->where('fees_type_id', $fees_master->fees_type_id)->sum('amount');
                }
            }

            foreach ($fees_assigns as $fees_assign) {
                $fees_master = SmFeesMaster::where('id', $fees_assign->fees_master_id)->first();
                $total_assign = $total_assign + $fees_master->amount;
            }
        }


        $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['classes'] = $classes->toArray();
            $data['students'] = $students->toArray();
            $data['assign_subjects'] = $assign_subjects;
            $data['assign_class_teachers'] = $assign_class_teachers;
            $data['total_collection'] = $total_collection;
            $data['total_assign'] = $total_assign;
            $data['class'] = $class;
            $data['section'] = $section;
            return ApiBaseMethod::sendResponse($data, null);
        }

        return view('backEnd.reports.class_report', compact('classes', 'students', 'assign_subjects', 'assign_class_teachers', 'total_collection', 'total_assign', 'class', 'section'));

        /*
        try {
            $class = SmClass::where('id', $request->class)->first();

            $section = $request->section !== '' ? SmSection::where('id', $request->section)->first() : '';

            $students = SmStudent::query();
            $students->where('active_status', 1);
            if ($request->section !== '') {
                $students->where('section_id', $request->section);
            }

            $students->where('class_id', $request->class);
            $students = $students->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $assign_subjects = SmAssignSubject::query();
            $assign_subjects->where('active_status', 1);
            if ($request->section !== '') {
                $assign_subjects->where('section_id', $request->section);
            }

            $assign_subjects->where('class_id', $request->class);
            $assign_subjects = $assign_subjects->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $assign_class_teacher = SmAssignClassTeacher::query();
            $assign_class_teacher->where('active_status', 1);
            if ($request->section !== '') {
                $assign_class_teacher->where('section_id', $request->section);
            }

            $assign_class_teacher->where('class_id', $request->class);
            $assign_class_teacher = $assign_class_teacher->first();
            $assign_class_teachers = $assign_class_teacher !== '' ? $assign_class_teacher->classTeachers->first() : '';

            $total_collection = 0;
            $total_assign = 0;
            foreach ($students as $student) {
                $fees_assigns = SmFeesAssign::where('student_id', $student->id)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                foreach ($fees_assigns as $fees_assign) {
                    $fees_masters = SmFeesMaster::where('id', $fees_assign->fees_master_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                    foreach ($fees_masters as $fee_master) {
                        $total_collection += SmFeesPayment::where('active_status', 1)->where('student_id', $student->id)->where('fees_type_id', $fee_master->fees_type_id)->sum('amount');
                    }
                }

                foreach ($fees_assigns as $fee_assign) {
                    $fees_master = SmFeesMaster::where('id', $fee_assign->fees_master_id)->first();
                    $total_assign += $fees_master->amount;
                }
            }

            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();
                $data['assign_subjects'] = $assign_subjects;
                $data['assign_class_teachers'] = $assign_class_teachers;
                $data['total_collection'] = $total_collection;
                $data['total_assign'] = $total_assign;
                $data['class'] = $class;
                $data['section'] = $section;

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.reports.class_report', ['classes' => $classes, 'students' => $students, 'assign_subjects' => $assign_subjects, 'assign_class_teachers' => $assign_class_teachers, 'total_collection' => $total_collection, 'total_assign' => $total_assign, 'class' => $class, 'section' => $section]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
