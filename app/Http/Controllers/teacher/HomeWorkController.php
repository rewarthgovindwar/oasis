<?php

namespace App\Http\Controllers\teacher;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmAssignSubject;
use App\SmHomework;
use App\SmStaff;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeWorkController extends Controller
{


    public function addHomework(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'class' => 'required',
                'section' => 'required',
                'subject' => 'required',
                'assign_date' => 'required',
                'submission_date' => 'required',
                'description' => 'required',
                'marks' => 'required',
            ]);
        }

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        }

            $fileName = '';
            if ($request->file('homework_file') !== '') {

                $file = $request->file('homework_file');
                $fileName = $request->teacher_id.time().'.'.$file->getClientOriginalExtension();
                // $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/homework/', $fileName);
                $fileName = 'public/uploads/homework/'.$fileName;
            }

            $smHomework = new SmHomework;
            $smHomework->class_id = $request->class;
            $smHomework->section_id = $request->section;
            $smHomework->subject_id = $request->subject;
            $smHomework->marks = $request->marks;
            $smHomework->created_by = $request->teacher_id;
            $smHomework->homework_date = $request->assign_date;
            $smHomework->submission_date = $request->submission_date;
            $smHomework->school_id = Auth::user()->school_id;
            $smHomework->academic_id = getAcademicId();
            // $homeworks->marks = $request->marks;
            $smHomework->description = $request->description;
            $smHomework->file = $fileName;
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $results = $smHomework->save();

                return ApiBaseMethod::sendResponse($results, null);
            }
            else{
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
    }

    public function homeworkList(Request $request, $id)
    {
            $teacher = SmStaff::where('user_id', '=', $id)->first();
            $teacher_id = $teacher->id;
            $subject_list = SmAssignSubject::where('teacher_id', '=', $teacher_id)->where('school_id', Auth::user()->school_id)->get();
            $i = 0;
            foreach ($subject_list as $subject) {
                $homework_subject_list[$subject->subject->subject_name] = $subject->subject->subject_name;
                $allList[$subject->subject->subject_name] = DB::table('sm_homeworks')
                    ->leftjoin('sm_subjects', 'sm_subjects.id', '=', 'sm_homeworks.subject_id')
                    ->where('sm_homeworks.created_by', $teacher_id)
                    ->where('subject_id', $subject->subject_id)->get()->toArray();
            }

            // return $allList;
            foreach ($allList as $single) {
                foreach ($single as $singleHw) {
                    $std_homework = DB::table('sm_homework_students')
                        ->select('homework_id', 'complete_status')
                        ->where('homework_id', '=', $singleHw->id)
                        ->where('complete_status', 'C')
                        ->first();

                    $d['homework_id'] = $singleHw->id;
                    $d['description'] = $singleHw->description;
                    $d['subject_name'] = $singleHw->subject_name;
                    $d['homework_date'] = $singleHw->homework_date;
                    $d['submission_date'] = $singleHw->submission_date;
                    $d['evaluation_date'] = $singleHw->evaluation_date;
                    $d['file'] = $singleHw->file;
                    $d['marks'] = $singleHw->marks;

                    $d['status'] = empty($std_homework) ? 'I' : 'C';
                    $status[] = $d;
                }
            }

            $data = [];

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                $data = $status;

                return ApiBaseMethod::sendResponse($data, null);
            }
            else{
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
    }
}
