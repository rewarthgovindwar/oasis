<?php

namespace App\Http\Controllers\Admin\Examination;

use Exception;
use App\SmExam;
use App\SmClass;
use App\SmExamType;
use App\SmMarkStore;
use App\SmMarksGrade;
use App\SmExamSetting;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\Models\ExamMeritPosition;
use App\Models\AllExamWisePosition;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Examination\SmExamFormatSettingsRequest;

class SmExamFormatSettingsController extends Controller
{
    public function index()
    {
        /*
        try {
        */
        $content_infos = SmExamSetting::with('examName')->get();

        $exams = SmExamType::get();

        $already_assigned = [];
        foreach ($content_infos as $content_info) {
            $already_assigned[] = $content_info->exam_type;
        }

        return view('backEnd.examination.exam_settings', ['content_infos' => $content_infos, 'exams' => $exams, 'already_assigned' => $already_assigned]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmExamFormatSettingsRequest $smExamFormatSettingsRequest)
    {
        /*
                try {
                */
        $destination = 'public/uploads/exam/';
        $smExamSetting = new SmExamSetting();
        $smExamSetting->exam_type = $smExamFormatSettingsRequest->exam_type;
        $smExamSetting->title = $smExamFormatSettingsRequest->title;
        $smExamSetting->publish_date = date('Y-m-d', strtotime($smExamFormatSettingsRequest->publish_date));
        $smExamSetting->file = fileUpload($smExamFormatSettingsRequest->file, $destination);
        $smExamSetting->start_date = $smExamFormatSettingsRequest->start_date ? date('Y-m-d', strtotime($smExamFormatSettingsRequest->start_date)) : null;
        $smExamSetting->end_date = $smExamFormatSettingsRequest->end_date ? date('Y-m-d', strtotime($smExamFormatSettingsRequest->end_date)) : null;
        $smExamSetting->school_id = Auth::user()->school_id;
        $smExamSetting->academic_id = getAcademicId();
        $smExamSetting->save();

        Toastr::success('Operation successful', 'Success');

        return redirect('exam-settings');
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
        $content_infos = SmExamSetting::with('examName')->get();

        $editData = SmExamSetting::where('id', $id)->first();

        $exams = SmExamType::get();

        $already_assigned = [];
        foreach ($content_infos as $content_info) {
            if ($editData->exam_type != $content_info->exam_type) {
                $already_assigned[] = $content_info->exam_type;
            }
        }

        // return $already_assigned;
        return view('backEnd.examination.exam_settings', ['editData' => $editData, 'content_infos' => $content_infos, 'exams' => $exams, 'already_assigned' => $already_assigned]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmExamFormatSettingsRequest $smExamFormatSettingsRequest)
    {

        /*
        try {
        */
        $destination = 'public/uploads/exam/';
        $update_add_content = SmExamSetting::find($smExamFormatSettingsRequest->id);
        $update_add_content->exam_type = $smExamFormatSettingsRequest->exam_type;
        $update_add_content->title = $smExamFormatSettingsRequest->title;
        $update_add_content->publish_date = date('Y-m-d', strtotime($smExamFormatSettingsRequest->publish_date));
        $update_add_content->start_date = $smExamFormatSettingsRequest->start_date ? date('Y-m-d', strtotime($smExamFormatSettingsRequest->start_date)) : null;
        $update_add_content->end_date = $smExamFormatSettingsRequest->end_date ? date('Y-m-d', strtotime($smExamFormatSettingsRequest->end_date)) : null;
        $update_add_content->school_id = Auth::user()->school_id;
        $update_add_content->academic_id = getAcademicId();
        $update_add_content->file = fileUpdate($update_add_content->file, $smExamFormatSettingsRequest->file, $destination);
        $result = $update_add_content->save();

        Toastr::success('Operation successful', 'Success');

        return redirect('exam-settings');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete($id)
    {
        /*
        try {
        */
        $content = SmExamSetting::find($id);
        if ($content->file != '' && file_exists($content->file)) {
            unlink($content->file);
        }

        $content->delete();
        Toastr::success('Operation successful', 'Success');

        return redirect('exam-settings');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examReportPosition()
    {
        /*
        try {
        */
        $exams = SmExamType::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

        $classes = SmClass::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

        return view('backEnd.examination.examPositionReport', ['exams' => $exams, 'classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function universityExamReportPositionStore($request)
    {

        $request->validate([
            'exam' => 'required',
            'un_semester_label_id' => 'required',
            'un_session_id' => 'required',
            'un_faculty_id' => 'nullable',
            'un_department_id' => 'nullable',
        ]);
        /*
        try {
        */
        $exam = $request->exam;
        $un_semester_label_id = $request->un_semester_label_id;
        $un_section_id = $request->un_section_id;

        $students = StudentRecord::with(['studentDetail' => function ($q) {
            return $q->where('active_status', 1);
        }])
            ->where('un_semester_label_id', $un_semester_label_id)
            ->where('un_section_id', $un_section_id)
            ->orderBy('id', 'asc')
            ->get();

        $fail_grade = SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->min('gpa');

        $max_gpa = SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->max('gpa');

        $totalSubject = SmMarkStore::where('un_semester_label_id', $un_semester_label_id)
            ->where('un_section_id', $un_section_id)
            ->get()
            ->unique();

        $passStudents = [];
        $failStudents = [];

        foreach ($students as $student) {
            $studentMarks = SmMarkStore::where('exam_term_id', $exam)
                ->where('student_record_id', $student->id)
                ->get()
                ->groupBy('un_subject_id');

            foreach ($studentMarks as $un_subject_id => $studentMark) {
                if (markGpa(subjectPercentageMark(@$studentMark->sum('total_marks'), @subjectFullMark($exam, $un_subject_id)))->gpa != $fail_grade) {
                    $passStudents[] = $student->id;
                } else {
                    $failStudents[] = $student->id;
                }
            }
        }

        $studenInfos = array_diff(array_unique($passStudents), array_unique($failStudents));

        if ($studenInfos != []) {
            $students = StudentRecord::whereIn('id', $studenInfos)->get();

            ExamMeritPosition::where('un_semester_label_id', $un_semester_label_id)
                ->where('un_section_id', $un_section_id)
                ->where('exam_term_id', $exam)
                ->delete();

            foreach ($students as $student) {
                $allMarks = SmMarkStore::where('exam_term_id', $exam)
                    ->where('student_record_id', $student->id)
                    ->get()
                    ->groupBy('un_subject_id');

                $totalGpa = 0;
                $totalMark = 0;
                foreach ($allMarks as $un_subject_id => $allMark) {
                    $totalMark += $allMark->sum('total_marks');
                    $totalGpa += markGpa(subjectPercentageMark(@$allMark->sum('total_marks'), @subjectFullMark($exam, $un_subject_id)))->gpa;

                }

                $gpa = $totalGpa / $totalSubject->count();
                $gpaData = $gpa > $max_gpa ? $max_gpa : $gpa;

                $data = new ExamMeritPosition();
                $data->un_semester_label_id = $un_semester_label_id;
                $data->un_section_id = $un_section_id;
                $data->un_faculty_id = $request->un_faculty_id;
                $data->un_department_id = $request->un_department_id;
                $data->un_session_id = $request->un_session_id;
                $data->exam_term_id = $exam;
                $data->total_mark = $totalMark;
                $data->gpa = number_format($gpaData, 2);
                $data->grade = gpaResult($gpaData)->grade_name;
                $data->admission_no = $student->studentDetail->roll_no;
                $data->record_id = $student->id;
                $data->school_id = auth()->user()->school_id;
                $data->un_academic_id = getAcademicId();
                $data->save();
            }

            $allStudentMarks = ExamMeritPosition::where('un_semester_label_id', $un_semester_label_id)
                ->where('un_section_id', $un_section_id)
                ->where('exam_term_id', $exam)
                ->orderBy('gpa', 'desc')
                ->get()
                ->sort(function ($a, $b) {
                    if ($a->gpa == $b->gpa) {
                        return $b->total_mark <=> $a->total_mark; // Descending total marks
                    }
                    return $b->gpa <=> $a->gpa; // Descending GPA
                });

            $position = 1;

            foreach ($allStudentMarks as $allStudentMark) {
                $new_position = ExamMeritPosition::where('id',$allStudentMark->id)->first();
                $new_position->position = $position;
                $new_position->save();
                $position++;
            }
        }

        Toastr::success('Operation successful', 'Success');

        return redirect()->route('exam-report-position');
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examReportPositionStore(Request $request)
    {
        
        if (moduleStatusCheck('University')) {
            return $this->universityExamReportPositionStore($request);
        }

        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'nullable',
        ]);

        $exam = $request->exam;
        $class = $request->class;
        $section = $request->section;
        $shift = shiftEnable() ? $request->shift : '';

        $studentsQuery = StudentRecord::with(['studentDetail' => function ($q) {
            $q->where('active_status', 1);
        }])
        ->where('class_id', $class);
        if ($section) {
            $studentsQuery->where('section_id', $section);
        }
        if ($shift) {
            $studentsQuery->where('shift_id', $shift);
        }
        $students = $studentsQuery->orderBy('id', 'asc')->get();

        $fail_grade = SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->min('gpa');

        $max_gpa = SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->max('gpa');

        $totalSubjectQuery = SmMarkStore::where('class_id', $class);
        if ($section) {
            $totalSubjectQuery->where('section_id', $section);
        }
        if ($shift) {
            $totalSubjectQuery->where('shift_id', $shift);
        }
        $totalSubject = $totalSubjectQuery->get()->unique();

        $passStudents = [];
        $failStudents = [];

        foreach ($students as $student) {
            $studentMarks = SmMarkStore::where('exam_term_id', $exam)
                ->where('student_record_id', $student->id)
                ->get()
                ->groupBy('subject_id');

            foreach ($studentMarks as $subject_id => $studentMark) {
                if (markGpa(subjectPercentageMark(@$studentMark->sum('total_marks'), @subjectFullMark($exam, $subject_id, $class, $section, $shift)))->gpa != $fail_grade) {
                    $passStudents[] = $student->id;
                } else {
                    $failStudents[] = $student->id;
                }
            }
        }
        
        $studenInfos = array_diff(array_unique($passStudents), array_unique($failStudents));
        if (!empty($studenInfos)) {
            $students = StudentRecord::whereIn('id', $studenInfos)->get();
            
            $positionDeleteQuery = ExamMeritPosition::where('class_id', $class)
                ->where('exam_term_id', $exam);
                if ($section) {
                    $positionDeleteQuery->where('section_id', $section);
                }
                if ($shift) {
                    $positionDeleteQuery->where('shift_id', $shift);
                }
                $positionDeleteQuery->delete();
                
            foreach ($students as $student) {
                $allMarks = SmMarkStore::where('exam_term_id', $exam)
                    ->where('student_record_id', $student->id)
                    ->get()
                    ->groupBy('subject_id');

                $totalGpa = 0;
                $totalMark = 0;
                
                foreach ($allMarks as $subject_id => $allMark) {
                    $totalMark += $allMark->sum('total_marks');
                    $totalGpa += markGpa(subjectPercentageMark(@$allMark->sum('total_marks'), @subjectFullMark($exam, $subject_id, $class, $section, $shift)))->gpa;
                }

                $gpa = $totalGpa / $allMarks->count();
                // dd($gpa);
                $gpaData = $gpa > $max_gpa ? $max_gpa : $gpa;

                $data = new ExamMeritPosition();
                $data->class_id = $class;
                $data->section_id = $section;
                $data->shift_id = $shift;
                $data->exam_term_id = $exam;
                $data->total_mark = $totalMark;
                $data->gpa = number_format($gpaData, 2);
                $data->grade = gpaResult($gpaData)->grade_name;
                $data->admission_no = $student->studentDetail->roll_no;
                $data->record_id = $student->id;
                $data->school_id = auth()->user()->school_id;
                $data->academic_id = getAcademicId();
                $data->save();
            }

            $allStudentMarks = ExamMeritPosition::where('class_id', $class)
                ->where('section_id', $section)
                ->when(shiftEnable(), function ($query) use ($shift) {
                    $query->where('shift_id', $shift);
                })
                ->where('exam_term_id', $exam)
                ->orderBy('gpa', 'desc')
                ->get()
                ->sort(function ($a, $b) {
                    if ($a->gpa == $b->gpa) {
                        return $b->total_mark <=> $a->total_mark; // Descending total marks
                    }
                    return $b->gpa <=> $a->gpa; // Descending GPA
                });

            $position = 1;

            foreach ($allStudentMarks as $allStudentMark) {

                $new_position = ExamMeritPosition::where('id',$allStudentMark->id)->first();
                $new_position->position = $position;
                $new_position->save();
                $position++;
            }
        }
       
        Toastr::success('Operation successful', 'Success');
        return redirect()->route('exam-report-position');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */

    }

    public function allExamReportPosition()
    {
        $exams = SmExamType::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

        $classes = SmClass::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

        return view('backEnd..examination.allExamPositionReport', ['exams' => $exams, 'classes' => $classes]);

    }

    public function universityAllExamReportPositionStore($request)
    {

        $request->validate([
            'un_semester_label_id' => 'required',
            'un_section_id' => 'required',
        ],
            [
                'un_semester_label_id.required' => 'Semester field is required',
                'un_section_id.required' => 'Section field is required',
            ]);
        /*
        try {
        */
        $un_semester_label_id = $request->un_semester_label_id;
        $un_section_id = $request->un_section_id;

        $students = StudentRecord::with(['studentDetail' => function ($q) {
            return $q->where('active_status', 1);
        }])
            ->where('un_semester_label_id', $un_semester_label_id)
            ->where('un_section_id', $un_section_id)
            ->whereHas('studentDetail', function ($q) {
                return $q->where('active_status', 1);
            })
            ->where('un_academic_id', getAcademicId())
            ->where('is_promote', 0)
            ->distinct('id')
            ->get();

        $fail_grade = SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->min('gpa');

        $max_gpa = SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->max('gpa');

        $totalSubject = SmMarkStore::where('un_semester_label_id', $un_semester_label_id)
            ->where('un_section_id', $un_section_id)
            ->distinct('exam_term_id')
            ->get()
            ->unique()->count();

        $passStudents = [];
        $failStudents = [];

        foreach ($students as $student) {
            $studentMarks = SmMarkStore::where('student_record_id', $student->id)
                ->where('un_academic_id', getAcademicId())
                ->select('un_subject_id', 'exam_term_id')->get()
                ->groupBy('un_subject_id');

            foreach ($studentMarks as $un_subject_id => $studentMark) {
                $dataGroup = $studentMark->groupBy('exam_term_id');
                foreach ($dataGroup as $exam_term_id => $data) {
                    $subFullMark = subjectFullMark($exam_term_id, $un_subject_id, $un_semester_label_id, $un_section_id);
                    if (markGpa(subjectPercentageMark($data->sum('total_marks'), $subFullMark))->gpa != $fail_grade) {
                        $passStudents[] = $student->id;
                    } else {
                        $failStudents[] = $student->id;
                    }

                }
            }
        }

        $studenInfos = array_diff(array_unique($passStudents), array_unique($failStudents));

        if ($studenInfos != []) {
            $students = StudentRecord::whereIn('id', $studenInfos)->get();

            AllExamWisePosition::where('un_semester_label_id', $un_semester_label_id)
                ->where('un_section_id', $un_section_id)
                ->delete();

            foreach ($students as $student) {
                $allMarks = SmMarkStore::where('student_record_id', $student->id)
                    ->where('un_academic_id', getAcademicId())
                    ->select('un_subject_id')->get()
                    ->groupBy('un_subject_id');

                $totalGpa = 0;
                $totalMark = 0;
                $examTerm = 0;
                foreach ($allMarks as $allMark) {
                    foreach ($allMark as $allMarke) {
                        $fullMark = subjectFullMark($allMarke->exam_term_id, $allMarke->un_subject_id);
                        $totalMark += subjectPercentageMark($allMarke->total_marks, $fullMark);
                        $totalGpa += markGpa(subjectPercentageMark($allMarke->total_marks, $fullMark))->gpa;
                        $examTerm += $allMarke->exam_term_id;
                    }
                }

                $gpa = $totalGpa / ($totalSubject * $examTerm);
                $gpaData = $gpa > $max_gpa ? $max_gpa : $gpa;

                $data = new AllExamWisePosition();
                $data->un_semester_label_id = $un_semester_label_id;
                $data->un_section_id = $un_section_id;
                $data->total_mark = $totalMark;
                $data->gpa = number_format($gpaData, 2);
                $data->grade = gpaResult($gpaData)->grade_name;
                $data->admission_no = $student->studentDetail->roll_no;
                $data->roll_no = $student->studentDetail->roll_no;
                $data->record_id = $student->id;
                $data->school_id = auth()->user()->school_id;
                $data->academic_id = getAcademicId();
                $data->save();
            }

            $allStudentMarks = AllExamWisePosition::where('un_semester_label_id', $un_semester_label_id)
                ->where('un_section_id', $un_section_id)
                ->orderBy('gpa', 'desc')
                ->get()
                ->sort(function ($a, $b) {
                   if ($a->gpa == $b->gpa) {
                        return $b->total_mark <=> $a->total_mark; // Descending total marks
                    }
                    return $b->gpa <=> $a->gpa; // Descending GPA
                });

            $position = 1;
            $last_mark = null;

            foreach ($allStudentMarks as $allStudentMark) {

                $new_position = AllExamWisePosition::where('id',$allStudentMark->id)->first();
                $new_position->position = $position;
                $new_position->save();
                $position++;
            }
        }

        Toastr::success('Operation successful', 'Success');

        return redirect()->route('all-exam-report-position');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function allExamReportPositionStore(Request $request)
    {
        // try {
        
            $request->validate([
                'class' => 'required',
                'section' => 'nullable',
            ]);

            $class = $request->class;
            $section = $request->section;
            $shift = $request->shift;

            $studentsQuery = StudentRecord::with(['studentDetail' => function ($q) {
                $q->where('active_status', 1);
            }])
            ->where('class_id', $class);
            if ($section) {
                $studentsQuery->where('section_id', $section);
            }
            if ($shift) {
                $studentsQuery->where('shift_id', $shift);
            }
            $students = $studentsQuery->where('academic_id', getAcademicId())
                ->where('is_promote', 0)->orderBy('id', 'asc')->get();

            $examTermQuery = SmExam::where('class_id', $class);
            if ($section) {
                $examTermQuery->where('section_id', $section);
            }
            if ($shift) {
                $examTermQuery->where('shift_id', $shift);
            }
            $examTerm = $examTermQuery->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->groupBy('exam_type_id')
                ->get()
                ->unique()
                ->count();

            $fail_grade = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $max_gpa = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->max('gpa');

            $passStudents = [];
            $failStudents = [];

            foreach ($students as $student) {
                $studentMarks = SmMarkStore::where('student_record_id', $student->id)
                    ->where('academic_id', getAcademicId())
                    ->get()
                    ->groupBy('subject_id');

                foreach ($studentMarks as $subject_id => $studentMark) {

                    $dataGroup = $studentMark->groupBy('exam_term_id');

                    foreach ($dataGroup as $exam_term_id => $data) {
                        $subFullMark = subjectFullMark($exam_term_id, $subject_id, $class, $section, $shift);
                        if (markGpa(subjectPercentageMark($data->sum('total_marks'), $subFullMark))->gpa != $fail_grade) {
                            $passStudents[] = $student->id;
                        } else {
                            $failStudents[] = $student->id;
                        }

                    }
                }
            }

            $studenInfos = $students->pluck('id');
            if ($studenInfos) {
                $students = StudentRecord::whereIn('id', $studenInfos)->get();
                $positionQuery = AllExamWisePosition::where('class_id', $class);
                if ($section) {
                    $positionQuery->where('section_id', $section);
                }
                if ($shift) {
                    $positionQuery->where('shift_id', $shift);
                }
                $positionQuery->delete();

                foreach ($students as $student) {
                    $allMarks = SmMarkStore::where('student_record_id', $student->id)
                        ->where('academic_id', getAcademicId())
                        ->where('total_marks','!=',0)
                        ->get()
                        ->groupBy('subject_id');
                    // dd($allMarks);

                    $totalGpa = 0;
                    $totalMark = 0;
                    $subjectGpa = 0;

                    foreach ($allMarks as $allMark) {
                        $subjectMark = 0;
                        foreach ($allMark as $allMarke) {
                            $fullMark = subjectFullMark($allMarke->exam_term_id, $allMarke->subject_id, $class, $section, $shift);
                            $totalMark += subjectPercentageMark($allMarke->total_marks, $fullMark);
                            $totalGpa += markGpa(subjectPercentageMark($allMarke->total_marks, $fullMark))->gpa;
                            $subjectMark += subjectPercentageMark($allMarke->total_marks, $fullMark);
                        }

                        $subjectGpa += markGpa($subjectMark / count($allMark))->gpa;

                    }
                    $gpa = $subjectGpa / $examTerm;
                    $gpaData = $gpa > $max_gpa ? $max_gpa : $gpa;
                    $data = new AllExamWisePosition();
                    $data->class_id = $class;
                    $data->section_id = $section ?? null;
                    $data->shift_id = $shift ?? null;
                    $data->total_mark = $totalMark;
                    $data->gpa = number_format($gpaData, 2);
                    $data->grade = gpaResult($gpaData)->grade_name;
                    $data->admission_no = $student->studentDetail->roll_no;
                    $data->roll_no = $student->studentDetail->roll_no;
                    $data->record_id = $student->id;
                    $data->school_id = auth()->user()->school_id;
                    $data->academic_id = getAcademicId();
                    $data->save();
                }

                $studentMarkQuery = AllExamWisePosition::where('class_id', $class);
                if ($section) {
                    $studentMarkQuery->where('section_id', $section);
                }
                if ($shift) {
                    $studentMarkQuery->where('shift_id', $shift);
                }

                $allStudentMarks = $studentMarkQuery->orderBy('gpa', 'desc')->get()->sort(function ($a, $b) {
                    if ($a->gpa == $b->gpa) {
                        return $b->total_mark <=> $a->total_mark; // Descending total marks
                    }
                    return $b->gpa <=> $a->gpa; // Descending GPA
                });

                $position = 1;
                foreach ($allStudentMarks as $allStudentMark) {
                   
                    $new_position = AllExamWisePosition::where('id',$allStudentMark->id)->first();
                    $new_position->position = $position;
                    $new_position->save();
                    $position++;
                }
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->route('all-exam-report-position');

        // } catch (Exception $exception) {
        //     Toastr::error('Operation Failed', 'Failed');

        //     return redirect()->back();
        // }
    }
}
