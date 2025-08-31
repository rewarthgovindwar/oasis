<?php

namespace App\Http\Controllers;

use App\Models\TeacherEvaluation;
use App\SmAssignSubject;
use App\SmClass;
use App\SmStaff;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherEvaluationReportController extends Controller
{
    public function getAssignSubjectTeacher(Request $request)
    {
        $staffs = SmAssignSubject::where('class_id', $request->class_id)->where('subject_id', $request->subject_id)->whereIn('section_id', $request->section_ids)->with('teacher')->select('teacher_id')->distinct('teacher_id')->get();
        return response()->json($staffs);
    }

    public function teacherApprovedEvaluationReport()
    {
        try {
            $classes = SmClass::select(['class_name', 'id'])->get();
            $teacherEvaluations = TeacherEvaluation::with('studentRecord.studentDetail.parents', 'staff')->get();

            return view('backEnd.teacherEvaluation.report.teacher_approved_evaluation_report', ['classes' => $classes, 'teacherEvaluations' => $teacherEvaluations]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function teacherPendingEvaluationReport()
    {
        $classes = SmClass::select(['class_name', 'id'])->get();
        $teacherEvaluations = TeacherEvaluation::with('studentRecord.studentDetail.parents', 'staff')->get();

        return view('backEnd.teacherEvaluation.report.teacher_pending_evaluation_report', ['classes' => $classes, 'teacherEvaluations' => $teacherEvaluations]);
    }

    public function teacherWiseEvaluationReport()
    {
        $classes = SmClass::select(['class_name', 'id'])->get();
        $teachers = SmStaff::where('role_id', 4)->get();
        $teacherEvaluations = TeacherEvaluation::with('studentRecord.studentDetail.parents', 'staff')->get();

        return view('backEnd.teacherEvaluation.report.teacher_wise_evaluation_report', ['classes' => $classes, 'teacherEvaluations' => $teacherEvaluations, 'teachers' => $teachers]);
    }

    public function teacherApprovedEvaluationReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class_id' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $classes = SmClass::select(['class_name', 'id'])->get();
            $staffs = SmAssignSubject::where('class_id', $request->class_id)
                ->when($request->subject_id, function ($query) use ($request): void {
                    $query->where('subject_id', $request->subject_id);
                })
                ->when($request->section_id, function ($query) use ($request): void {
                    $query->whereIn('section_id', [$request->section_id]);
                })
                ->when($request->teacher_id, function ($query) use ($request): void {
                    $query->where('teacher_id', $request->teacher_id);
                })->get();

            $teacherEvaluations = TeacherEvaluation::when($request->class_id, function ($q) use ($request): void {
                $q->whereHas('studentRecord', function ($query) use ($request): void {
                    $query->where('class_id', $request->class_id);
                });
            })
                ->when($request->subject_id, function ($q) use ($request): void {
                    $q->where('subject_id', $request->subject_id);
                })
                ->when($request->section_id, function ($q) use ($request): void {
                    $q->whereHas('studentRecord', function ($query) use ($request): void {
                        $query->where('section_id', $request->section_id);
                    });
                })
                ->when($request->teacher_id, function ($q) use ($staffs): void {
                    foreach ($staffs as $staff) {
                        $q->where('teacher_id', $staff->teacher_id);
                    }
                })
                ->when($request->submitted_by, function ($q) use ($request): void {
                    $q->where('role_id', $request->submitted_by);
                })
                ->with('studentRecord.studentDetail.parents', 'staff')->get();

            return view('backEnd.teacherEvaluation.report.teacher_approved_evaluation_report', ['classes' => $classes, 'teacherEvaluations' => $teacherEvaluations]);
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function teacherPendingEvaluationReportSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class_id' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $classes = SmClass::select(['class_name', 'id'])->get();
            $staffs = SmAssignSubject::where('class_id', $request->class_id)
                ->when($request->subject_id, function ($query) use ($request): void {
                    $query->where('subject_id', $request->subject_id);
                })
                ->when($request->section_id, function ($query) use ($request): void {
                    $query->whereIn('section_id', [$request->section_id]);
                })
                ->when($request->teacher_id, function ($query) use ($request): void {
                    $query->where('teacher_id', $request->teacher_id);
                })->get();

            $teacherEvaluations = TeacherEvaluation::when($request->class_id, function ($q) use ($request): void {
                $q->whereHas('studentRecord', function ($query) use ($request): void {
                    $query->where('class_id', $request->class_id);
                });
            })
                ->when($request->subject_id, function ($q) use ($request): void {
                    $q->where('subject_id', $request->subject_id);
                })
                ->when($request->section_id, function ($q) use ($request): void {
                    $q->whereHas('studentRecord', function ($query) use ($request): void {
                        $query->where('section_id', $request->section_id);
                    });
                })
                ->when($request->teacher_id, function ($q) use ($staffs): void {
                    foreach ($staffs as $staff) {
                        $q->where('teacher_id', $staff->teacher_id);
                    }
                })
                ->when($request->submitted_by, function ($q) use ($request): void {
                    $q->where('role_id', $request->submitted_by);
                })
                ->with('studentRecord.studentDetail.parents', 'staff')->get();

            return view('backEnd.teacherEvaluation.report.teacher_pending_evaluation_report', ['classes' => $classes, 'teacherEvaluations' => $teacherEvaluations]);
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function teacherWiseEvaluationReportSearch(Request $request)
    {
        try {
            $classes = SmClass::select(['class_name', 'id'])->get();
            $teachers = SmStaff::where('role_id', 4)->get();
            $teacherEvaluations = TeacherEvaluation::query();
            if ($request->teacher_id) {
                $teacherEvaluations->where('teacher_id', $request->teacher_id);
            }

            if ($request->submitted_by) {
                $teacherEvaluations->where('role_id', $request->submitted_by);
            }

            $teacherEvaluations = $teacherEvaluations->with('studentRecord.studentDetail.parents', 'staff')->get();

            return view('backEnd.teacherEvaluation.report.teacher_wise_evaluation_report', ['classes' => $classes, 'teacherEvaluations' => $teacherEvaluations, 'teachers' => $teachers]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function teacherEvaluationApproveSubmit($id)
    {
        try {
            $teacherEvaluations = TeacherEvaluation::find($id);
            $teacherEvaluations->status = 1;
            $teacherEvaluations->update();

            return redirect()->back();
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function teacherEvaluationApproveDelete($id)
    {
        try {
            $teacherEvaluations = TeacherEvaluation::find($id);
            $teacherEvaluations->delete();

            return redirect()->back();
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function teacherPanelEvaluationReport()
    {
        try {
            $staffId = SmStaff::where('user_id', auth()->user()->id)->select('id')->first();
            $teacherEvaluations = TeacherEvaluation::where('teacher_id', $staffId->id)->with('studentRecord')->get();

            return view('backEnd.teacherEvaluation.report.teacher_panel_evaluation_report', ['teacherEvaluations' => $teacherEvaluations]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
