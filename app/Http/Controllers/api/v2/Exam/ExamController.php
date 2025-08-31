<?php

namespace App\Http\Controllers\api\v2\Exam;

use App\Http\Controllers\Controller;
use App\Http\Resources\v2\ExamResource;
use App\Http\Resources\v2\ExamRoutineResource;
use App\Http\Resources\v2\OnlineExamResultResource;
use App\Http\Resources\v2\StudentOnlineExamResource;
use App\Models\StudentRecord;
use App\Scopes\AcademicSchoolScope;
use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmAcademicYear;
use App\SmAssignSubject;
use App\SmExam;
use App\SmExamSchedule;
use App\SmExamType;
use App\SmOnlineExam;
use App\SmResultStore;
use App\SmStudent;
use App\SmStudentTakeOnlineExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\OnlineExam\Entities\InfixOnlineExam;

class ExamController extends Controller
{
    public function studentExam(Request $request)
    {
        $record = StudentRecord::where('school_id', auth()->user()->school_id)->findOrFail($request->record_id);

        $exam = SmExam::withoutGlobalScopes([AcademicSchoolScope::class, GlobalAcademicScope::class])
            ->with(['examType' => function ($q): void {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }, 'class' => function ($q): void {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }, 'section' => function ($q): void {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }, 'subject' => function ($q): void {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }])
            ->where('class_id', $record->class_id)
            ->where('section_id', $record->section_id)
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
            ->where('active_status', 1)->get();

        $anonymousResourceCollection = ExamResource::collection($exam);

        if (! $anonymousResourceCollection) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $anonymousResourceCollection,
                'message' => 'Exam list',
            ];
        }

        return response()->json($response);
    }

    public function studentExamType(Request $request)
    {
        $record = StudentRecord::where('school_id', auth()->user()->school_id)->findOrFail($request->record_id);

        $exam = SmExamType::withoutGlobalScopes([StatusAcademicSchoolScope::class, GlobalAcademicScope::class])
            ->select('id', 'title')
            ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
            ->where('school_id', $record->school_id)->get();

        if (! $exam) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $exam,
                'message' => 'Exam type list',
            ];
        }

        return response()->json($response);
    }

    public function studentExamSchedule(Request $request)
    {
        $record = StudentRecord::where('school_id', auth()->user()->school_id)->findOrFail($request->record_id);

        $schedule = SmExamSchedule::withoutGlobalScope(AcademicSchoolScope::class)
            ->with(['class' => function ($q): void {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }, 'section' => function ($q): void {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }, 'subject' => function ($q): void {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }])
            ->where('class_id', $record->class_id)
            ->where('section_id', $record->section_id)
            ->where('exam_term_id', $request->exam_type_id)
            ->where('school_id', $record->school_id)
            ->orderBy('date', 'ASC')->get();

        $anonymousResourceCollection = ExamRoutineResource::collection($schedule);

        if (! $anonymousResourceCollection) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $anonymousResourceCollection,
                'message' => 'Exam routine list',
            ];
        }

        return response()->json($response);
    }

    public function examResult(Request $request)
    {
        $data = [];
        $record = StudentRecord::where('school_id', auth()->user()->school_id)->findOrFail($request->record_id);

        $exam = SmExamType::withoutGlobalScopes([StatusAcademicSchoolScope::class, GlobalAcademicScope::class])
            ->where('school_id', auth()->user()->school_id)
            ->find($request->exam_type_id);

        $get_results = SmStudent::getExamResult(@$exam->id, @$record);

        $result = [];
        if ($get_results) {
            foreach ($get_results as $get_result) {
                $result[] = [
                    'id' => (int) $get_result->id,
                    'exam_name' => (string) @$exam->title,
                    'subject_name' => (string) @$get_result->subject->subject_name,
                    'obtained_marks' => (float) $get_result->total_marks,
                    'total_marks' => (float) subjectFullMark($get_result->exam_type_id, $get_result->subject_id, $record->class_id, $record->section_id),
                    'grade' => (string) @$get_result->total_gpa_grade,
                ];
            }
        }

        $data['exam_result'] = $result;

        if ($data == []) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'Exam result',
            ];
        }

        return response()->json($response);
    }

    public function examResultSearch(Request $request)
    {
        $data = [];
        $record = StudentRecord::where('school_id', auth()->user()->school_id)->findOrFail($request->record_id);

        $exam = SmExamType::withoutGlobalScopes([StatusAcademicSchoolScope::class, GlobalAcademicScope::class])
            ->where('school_id', auth()->user()->school_id)->find($request->exam_id);

        $get_results = SmStudent::getExamResult(@$exam->id, @$record);
        $result = [];
        if ($get_results) {
            foreach ($get_results as $get_result) {
                $result[] = [
                    'id' => (int) $get_result->id,
                    'exam_name' => (string) @$exam->title,
                    'subject_name' => (string) @$get_result->subject->subject_name,
                    'obtained_marks' => (float) $get_result->total_marks,
                    'total_marks' => (float) subjectFullMark($get_result->exam_type_id, $get_result->subject_id, $record->class_id, $record->section_id),
                    'grade' => (float) @$get_result->total_gpa_grade,
                ];
            }
        }

        $data['exam_result'] = $result;
        if ($data == []) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'Exam search result',
            ];
        }

        return response()->json($response);
    }

    public function studentOnlineExam(Request $request)
    {
        $record = StudentRecord::where('school_id', auth()->user()->school_id)->findOrFail($request->record_id);

        $student_id = @$record->studentDetail->user_id;

        if (moduleStatusCheck('OnlineExam') == true) {
            if (moduleStatusCheck('University')) {
                $online_exam = InfixOnlineExam::selectRaw(sprintf('*, %s as student_id', $student_id))
                    ->where('active_status', 1)
                    ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                    ->where('status', 1)
                    ->where('un_faculty_id', $record->un_faculty_id)
                    ->where('un_department_id', $record->un_department_id)
                    ->where('un_semester_label_id', $record->un_semester_label_id)
                    ->where('school_id', auth()->user()->school_id)
                    ->get();
            }

            $online_exam = InfixOnlineExam::selectRaw(sprintf('*, %s as student_id', $student_id))
                ->with('studentSubmitExamWithStatus')
                ->where('active_status', 1)
                ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('status', 1)->where('class_id', $record->class_id)
                ->where('school_id', auth()->user()->school_id)
                ->get()->filter(function ($exam) {
                    $exam->when($exam->section_id, function ($q): void {
                        $q->where('section_id', $q->section_id);
                    });

                    return $exam;
                });
        }

        $online_exam = SmOnlineExam::withoutGlobalScopes([StatusAcademicSchoolScope::class])
            ->selectRaw(sprintf('*, %s as student_id', $student_id))
            ->whereDoesntHave('studentAttend')
            ->where('active_status', 1)
            ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
            ->where('status', 1)
            ->where('class_id', $record->class_id)
            ->where('section_id', $record->section_id)
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('id', 'DESC')->get();

        $anonymousResourceCollection = StudentOnlineExamResource::collection($online_exam);

        if (! $anonymousResourceCollection) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $anonymousResourceCollection,
                'message' => 'Online exam list',
            ];
        }

        return response()->json($response);
    }

    public function studentViewResult(Request $request)
    {
        $result = SmStudentTakeOnlineExam::where('active_status', 1)
            ->where('status', 2)
            ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
            ->when($request->online_exam_id, function ($q) use ($request): void {
                $q->where('online_exam_id', $request->online_exam_id);
            })
            ->where('student_id', $request->student_id)
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('id', 'DESC')
            ->get();

        $anonymousResourceCollection = OnlineExamResultResource::collection($result);

        if (! $anonymousResourceCollection) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $anonymousResourceCollection,
                'message' => 'Online exam result',
            ];
        }

        return response()->json($response);
    }

    private static function getExamResult($exam_id, $record)
    {
        $eligible_subjects = SmAssignSubject::where('class_id', $record->class_id)
            ->where('section_id', $record->section_id)

            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->select('subject_id')
            ->distinct(['section_id', 'subject_id'])
            ->get();

        foreach ($eligible_subjects as $eligible_subject) {
            $getMark = SmResultStore::where([
                ['exam_type_id', $exam_id],
                ['student_id', $record->student_id],
                ['student_record_id', $record->id],
                ['subject_id', $eligible_subject->subject_id],
            ])->first();

            if ($getMark == '') {
                continue;
            }

            return SmResultStore::where([
                ['exam_type_id', $exam_id],
                ['student_id', $record->student_id],
                ['student_record_id', $record->id],
            ])->get();
        }

        return [];
    }
}
