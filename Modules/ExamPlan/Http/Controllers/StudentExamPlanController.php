<?php

namespace Modules\ExamPlan\Http\Controllers;

use App\Models\StudentRecord;
use App\SmAssignSubject;
use App\SmExam;
use App\SmExamSchedule;
use App\SmStudent;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\ExamPlan\Entities\AdmitCard;
use Modules\ExamPlan\Entities\AdmitCardSetting;

class StudentExamPlanController extends Controller
{
    public function admitCard()
    {
        try {
            $student = Auth::user()->student;
            $records = StudentRecord::where('is_promote', 0)
                ->where('student_id', $student->id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('examplan::studentAdmitCard', ['records' => $records]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Error');

            return redirect()->back();
        }

    }

    public function admitCardSearch(Request $request)
    {
        try {
            $smExam = SmExam::findOrFail($request->exam);
            if (auth()->user()->role_id === 3) {
                $student = SmStudent::find($request->student_id);
            } else {
                $student = Auth::user()->student;
            }

            $studentRecord = StudentRecord::where('student_id', $student->id)
                ->where('class_id', $smExam->class_id)
                ->where('section_id', $smExam->section_id)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->first();

            $exam_routines = SmExamSchedule::where('class_id', $smExam->class_id)
                ->where('section_id', $smExam->section_id)
                ->where('exam_term_id', $smExam->exam_type_id)
                ->orderBy('date', 'ASC')
                ->get();
            if ($exam_routines) {

                $admit = AdmitCard::where('academic_id', getAcademicId())
                    ->where('student_record_id', $studentRecord->id)
                    ->where('exam_type_id', $smExam->exam_type_id)
                    ->first();
                if ($admit) {
                    return redirect()->route('examplan.admitCardDownload', $admit->id);
                }

                Toastr::warning('Admit Card Not Pulished Yet', 'Warning');

                return redirect()->back();

            }

            Toastr::warning('Exam Routine Not Pulished Yet', 'Warning');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Error');

            return redirect()->back();
        }

    }

    public function admitCardDownload($id)
    {
        try {

            $admit = AdmitCard::find($id);
            $studentRecord = StudentRecord::find($admit->student_record_id);
            $student = SmStudent::find($studentRecord->student_id);
            $setting = AdmitCardSetting::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->first();
            $assign_subjects = SmAssignSubject::where('class_id', $studentRecord->class_id)->where('section_id', $studentRecord->section_id)
                ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_routines = SmExamSchedule::where('class_id', $studentRecord->class_id)
                ->where('section_id', $studentRecord->section_id)
                ->where('exam_term_id', $admit->exam_type_id)->orderBy('date', 'ASC')->get();

            if ($setting->admit_layout === 1) {
                return view('examplan::studentAdmitCardDownload', ['setting' => $setting, 'assign_subjects' => $assign_subjects, 'exam_routines' => $exam_routines, 'studentRecord' => $studentRecord, 'student' => $student, 'admit' => $admit]);
            }

            return view('examplan::studentAdmitCardDownload_two', ['setting' => $setting, 'assign_subjects' => $assign_subjects, 'exam_routines' => $exam_routines, 'studentRecord' => $studentRecord, 'student' => $student, 'admit' => $admit]);

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Error');

            return redirect()->back();
        }
    }

    public function admitCardParent($student_id)
    {
        try {
            $records = StudentRecord::where('is_promote', 0)
                ->where('student_id', $student_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('examplan::studentAdmitCard', ['records' => $records, 'student_id' => $student_id]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Error');

            return redirect()->back();
        }
    }
}
