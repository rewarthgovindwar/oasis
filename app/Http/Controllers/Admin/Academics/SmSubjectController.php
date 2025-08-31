<?php

namespace App\Http\Controllers\Admin\Academics;

use Exception;
use App\SmSubject;
use App\tableList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\Academics\SmSubjectRequest;

class SmSubjectController extends Controller
{
    public function index(Request $request)
    {

        /*
        try {
        */
            $subjects = SmSubject::orderBy('id', 'DESC')->get();

            return view('backEnd.academics.subject', ['subjects' => $subjects]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmSubjectRequest $smSubjectRequest)
    {
        /*
        try {
        */
            $smSubject = new SmSubject();
            $smSubject->subject_name = $smSubjectRequest->subject_name;
            $smSubject->subject_type = $smSubjectRequest->subject_type;
            $smSubject->subject_code = $smSubjectRequest->subject_code;
            if (@generalSetting()->result_type == 'mark') {
                $smSubject->pass_mark = $smSubjectRequest->pass_mark;
            }

            $smSubject->created_by = auth()->user()->id;
            $smSubject->school_id = auth()->user()->school_id;
            $smSubject->academic_id = getAcademicId();
            $result = $smSubject->save();
            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit($id)
    {
        /*
        try {
        */
            $subject = SmSubject::find($id);
            $subjects = SmSubject::orderBy('id', 'DESC')->get();

            return view('backEnd.academics.subject', ['subject' => $subject, 'subjects' => $subjects]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmSubjectRequest $smSubjectRequest)
    {
        /*
        try {
        */
            $subject = SmSubject::find($smSubjectRequest->id);
            $subject->subject_name = $smSubjectRequest->subject_name;
            $subject->subject_type = $smSubjectRequest->subject_type;
            $subject->subject_code = $smSubjectRequest->subject_code;
            if (@generalSetting()->result_type == 'mark') {
                $subject->pass_mark = $smSubjectRequest->pass_mark;
            }

            $subject->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->route('subject');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function delete(Request $request, $id)
    {
        /*
        try {
        */
            $tables = tableList::getTableList('subject_id', $id);
            /*
            try {
            */
                if ($tables == null) {
                    // $delete_query = $section = SmSubject::destroy($id);
                         SmSubject::destroy($id);
                         Toastr::success('Operation successful', 'Success');
                         return redirect('subject');
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            /*
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
