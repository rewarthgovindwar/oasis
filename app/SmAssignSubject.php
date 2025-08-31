<?php

namespace App;

use Exception;
use App\Models\TeacherEvaluation;
use Illuminate\Support\Facades\DB;
use App\Scopes\GlobalAcademicScope;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmAssignSubject extends Model
{
    use HasFactory;

    protected $casts = [
        'class_id' => 'integer',
        'section_id' => 'integer',
        'active_status' => 'integer',
        'subject_id' => 'integer',
    ];

    public static function getNumberOfPart($subject_id, $class_id, $section_id, $exam_term_id, $shift_id = null)
    {
        try {
            
           return SmExamSetup::where('class_id', $class_id)
                        ->where('subject_id', $subject_id)
                        ->where('section_id', $section_id)
                        ->where('exam_term_id', $exam_term_id)
                        ->when(!empty($shift_id),function($q) use ($shift_id){
                            return $q->where('shift_id',$shift_id);
                        })
                        ->get();
            
            
           
        } catch (Exception $exception) {
            return null;
        }
    }

    public static function un_getNumberOfPart($subject_id, $exam_type, $request)
    {
        try {
            $SmExamSetup = SmExamSetup::query();

            return universityFilter($SmExamSetup, $request)
                ->where([
                    ['un_subject_id', $subject_id],
                    ['exam_term_id', $exam_type],
                ])
                ->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getNumberOfPartStudent($subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            return SmExamSetup::where([
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getMarksOfPart($student_id, $subject_id, $class_id, $section_id, $exam_term_id, $shift_id = null)
    {
        try {
            return SmMarkStore::where([
                ['student_id', $student_id],
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
                ['shift_id', $shift_id]
            ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function un_getMarksOfPart($student_id, $subject_id, $request, $exam_term_id)
    {
        try {
            $SmMarkStore = SmMarkStore::query();

            return universityFilter($SmMarkStore, $request)
                ->where([
                    ['student_id', $student_id],
                    ['un_subject_id', $subject_id],
                    ['exam_term_id', $exam_term_id],
                ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getSumMark($student_id, $subject_id, $class_id, $section_id, $exam_term_id, $shift_id = null)
    {
        try {
            return SmMarkStore::where([
                ['student_id', $student_id],
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
                ['shift_id', $shift_id]
            ])->sum('total_marks');
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function un_getSumMark($student_id, $subject_id, $request, $exam_term_id)
    {
        try {
            $SmMarkStore = SmMarkStore::query();

            return universityFilter($SmMarkStore, $request)
                ->where([
                    ['student_id', $student_id],
                    ['un_subject_id', $subject_id],
                    ['exam_term_id', $exam_term_id],
                ])->sum('total_marks');
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getHighestMark($subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            $results = DB::table('sm_mark_stores')
                ->select('student_id', DB::raw('SUM(total_marks) as total_amount'))
                ->where([
                    ['class_id', $class_id],
                    ['subject_id', $subject_id],
                    ['section_id', $section_id],
                    ['exam_term_id', $exam_term_id],
                ])
                ->distinct('student_id')
                ->get();
            $totalMark = [];
            foreach ($results as $result) {
                $totalMark[] = $result->total_amount;
            }

            return max($totalMark);
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getSubjectMark($subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            return SmExamSetup::where([
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->sum('exam_mark');
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function get_student_result($student_id, $subject_id, $class_id, $section_id, $exam_term_id, $optional_subject_id, $optional_subject_setup)
    {
        try {
            $this_student_failed = 0;
            $total_gpa_point = 0;
            $student_info = SmStudent::where('id', '=', $student_id)->first();
            $optional_subject = SmOptionalSubjectAssign::where('student_id', '=', $student_info->id)->where('session_id', '=', $student_info->session_id)->first();
            $subjects = self::where([['class_id', $class_id], ['section_id', $section_id]])->get();
            $assign_subjects = self::where([['class_id', $class_id], ['section_id', $section_id]])->get();
            foreach ($subjects as $subject) {
                $subject_id = $subject->subject_id;
                $total_mark = self::getSumMark($student_id, $subject_id, $class_id, $section_id, $exam_term_id);
                $mark_grade = SmMarksGrade::where([['percent_from', '<=', $total_mark], ['percent_upto', '>=', $total_mark]])->first();
                $optional_subject_id = '';
                if (! empty($optional_subject)) {
                    $optional_subject_id = $optional_subject->subject_id;
                }

                if ($subject_id == $optional_subject_id) {

                    // return $optional_subject_id;
                    if ($mark_grade->gpa < $optional_subject_setup->gpa_above) {
                        $total_gpa_point += 0;
                        if ($mark_grade->gpa < 1) {
                            $this_student_failed = 1;
                        }
                    } else {
                        $optional_mark_grade = $mark_grade->gpa - $optional_subject_setup->gpa_above;
                        $total_gpa_point += $optional_mark_grade;
                        if ($mark_grade->gpa < 1) {
                            $this_student_failed = 1;
                        }
                    }
                } else {
                    $total_gpa_point += $mark_grade->gpa;
                    if ($mark_grade->gpa < 1) {
                        $this_student_failed = 1;
                    }
                }
            }

            if ($this_student_failed != 1) {
                if ($optional_subject_id != '') {
                    $number_of_subject = count($assign_subjects);
                    $number_of_subject -= 1;
                    if ($total_gpa_point != 0 && $number_of_subject != 0) {
                        return number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                    }

                    return '0.00';

                }

                $number_of_subject = count($assign_subjects);

                if ($total_gpa_point != 0 && $number_of_subject != 0) {
                    return number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                }

                return '0.00';

            }

            return '0.00';

        } catch (Exception $exception) {
            return [];
        }
    }

    public static function get_student_result_without_optional($student_id, $subject_id, $class_id, $section_id, $exam_term_id, $optional_subject_id, $optional_subject_setup)
    {
        try {
            $this_student_failed = 0;
            $total_gpa_point = 0;
            $student_info = SmStudent::where('id', '=', $student_id)->first();
            $optional_subject = SmOptionalSubjectAssign::where('student_id', '=', $student_info->id)->where('session_id', '=', $student_info->session_id)->first();

            $subjects = self::where([['class_id', $class_id], ['section_id', $section_id]])->get();
            $assign_subjects = self::where([['class_id', $class_id], ['section_id', $section_id]])->get();
            foreach ($subjects as $subject) {
                $subject_id = $subject->subject_id;
                $total_mark = self::getSumMark($student_id, $subject_id, $class_id, $section_id, $exam_term_id);
                $mark_grade = SmMarksGrade::where([['percent_from', '<=', $total_mark], ['percent_upto', '>=', $total_mark]])->first();
                $optional_subject_id = '';
                if (! empty($optional_subject)) {
                    $optional_subject_id = $optional_subject->subject_id;
                }

                $total_gpa_point += $mark_grade->gpa;
                if ($mark_grade->gpa < 1) {
                    $this_student_failed = 1;
                }
            }

            if ($this_student_failed != 1) {
                if ($optional_subject_id != '') {

                    $number_of_subject = count($assign_subjects);
                    if ($total_gpa_point != 0 && $number_of_subject != 0) {
                        return number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                    }

                    return '0.00';

                }

                $number_of_subject = count($assign_subjects);

                if ($total_gpa_point != 0 && $number_of_subject != 0) {
                    return number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                }

                return '0.00';

            }

            return '0.00';

        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @return float[]
     */
    public static function subjectPosition($subject_id, $class_id, $custom_result): array
    {

        $students = SmStudent::where('class_id', $class_id)->get();

        $subject_mark_array = [];
        foreach ($students as $student) {
            $subject_marks = 0;

            $first_exam_mark = SmMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id1)->sum('total_marks');

            $subject_marks += $first_exam_mark / 100 * $custom_result->percentage1;

            $second_exam_mark = SmMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id2)->sum('total_marks');

            $subject_marks += $second_exam_mark / 100 * $custom_result->percentage2;

            $third_exam_mark = SmMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id3)->sum('total_marks');

            $subject_marks += $third_exam_mark / 100 * $custom_result->percentage3;

            $subject_mark_array[] = round($subject_marks);
        }

        arsort($subject_mark_array);

        return $subject_mark_array;
    }

    public function subject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    public function results()
    {
        return $this->hasMany(SmResultStore::class, 'subject_id', 'subject_id');
    }

    public function resultBySubject()
    {
        return $this->hasMany(SmResultStore::class, 'subject_id', 'subject_id')->where('section_id', $this->section_id)
            ->where('class_id', $this->class_id);
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(SmStaff::class, 'teacher_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function examSetups()
    {
        return $this->hasMany(SmExamSetup::class, 'class_id', 'class_id')->where('class_id', $this->class_id)
            ->where('section_id', $this->section_id);
    }

    public function markBySubject()
    {
        return $this->hasMany(SmMarkStore::class, 'subject_id', 'subject_id')->where('section_id', $this->section_id)
            ->where('class_id', $this->class_id);
    }

    public function exam()
    {
        return $this->hasOne(SmExam::class, 'subject_id', 'subject_id');
    }

    public function examSchedule()
    {
        return $this->hasMany(SmExamSchedule::class, 'subject_id', 'subject_id')
            ->where('class_id', $this->class_id)->where('section_id', $this->section_id);
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id);
    }

    public function teacherEvaluation()
    {
        return $this->hasMany(TeacherEvaluation::class, 'record_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
