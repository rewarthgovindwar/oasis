<?php

namespace App\Http\Controllers\Admin\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Examination\SmMarkGradeRequest;
use App\SmMarksGrade;
use App\SmResultStore;
use App\YearCheck;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmMarksGradeController extends Controller
{

    public function index(Request $request)
    {
        /*
        try {
        */
            $marks_grades = SmMarksGrade::orderBy('gpa', 'desc')->where('academic_id', getAcademicId())->get();

            return view('backEnd.examination.marks_grade', ['marks_grades' => $marks_grades]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmMarkGradeRequest $smMarkGradeRequest)
    {
        /*
        try {
        */
            if (SmResultStore::where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)->count() > 0) {
                Toastr::error('Exam Already Taken', 'Failed');

                return redirect()->back();
            }

            $smMarksGrade = new SmMarksGrade();
            $smMarksGrade->grade_name = $smMarkGradeRequest->grade_name;
            $smMarksGrade->gpa = $smMarkGradeRequest->gpa;
            $smMarksGrade->percent_from = $smMarkGradeRequest->percent_from;
            $smMarksGrade->percent_upto = $smMarkGradeRequest->percent_upto;
            $smMarksGrade->from = $smMarkGradeRequest->grade_from;
            $smMarksGrade->up = $smMarkGradeRequest->grade_upto;
            $smMarksGrade->description = $smMarkGradeRequest->description;
            $smMarksGrade->created_by = auth()->user()->id;
            $smMarksGrade->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
            $smMarksGrade->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smMarksGrade->un_academic_id = getAcademicId();
            } else {
                $smMarksGrade->academic_id = getAcademicId();
            }

            $result = $smMarksGrade->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show(Request $request, $id)
    {
        /*
        try {
        */
            $marks_grade = SmMarksGrade::find($id);
            if (moduleStatusCheck('University')) {
                $marks_grades = SmMarksGrade::where('un_academic_id', getAcademicId())->get();
            } else {
                $marks_grades = SmMarksGrade::where('academic_id', getAcademicId())->get();
            }

            return view('backEnd.examination.marks_grade', ['marks_grade' => $marks_grade, 'marks_grades' => $marks_grades]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmMarkGradeRequest $smMarkGradeRequest, $id)
    {
        /*
        try {
        */
            $marks_grade = SmMarksGrade::find($smMarkGradeRequest->id);
            $marks_grade->grade_name = $smMarkGradeRequest->grade_name;
            $marks_grade->gpa = $smMarkGradeRequest->gpa;
            $marks_grade->percent_from = $smMarkGradeRequest->percent_from;
            $marks_grade->percent_upto = $smMarkGradeRequest->percent_upto;
            $marks_grade->description = $smMarkGradeRequest->description;
            $marks_grade->from = $smMarkGradeRequest->grade_from;
            $marks_grade->updated_by = auth()->user()->id;
            $marks_grade->up = $smMarkGradeRequest->grade_upto;
            $result = $marks_grade->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('marks-grade');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request, $id)
    {
        /*
        try {
        */
            $marks_grade = SmMarksGrade::destroy($id);
            Toastr::success('Operation successful', 'Success');

            return redirect('marks-grade');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
