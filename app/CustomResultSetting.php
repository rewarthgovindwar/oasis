<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomResultSetting extends Model
{
    public static function getGpa($marks)
    {
        try {
            $marks_gpa = DB::table('sm_marks_grades')->where('percent_from', '<=', $marks)->where('percent_upto', '>=', $marks)->where('academic_id', getAcademicId())->first();

            return $marks_gpa->gpa;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getDrade($marks)
    {
        try {
            $marks_gpa = DB::table('sm_marks_grades')->where('percent_from', '<=', $marks)->where('percent_upto', '>=', $marks)->where('academic_id', getAcademicId())->first();

            return $marks_gpa->grade_name;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function gpaToGrade($gpa)
    {
        try {
            $marks_gpa = DB::table('sm_marks_grades')->where('from', '<=', $gpa)->where('up', '>=', $gpa)->where('academic_id', getAcademicId())->first();

            return $marks_gpa->grade_name;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function termResult($exam_id, $class_id, $section_id, $student_id, $subject_count)
    {
        try {
            $assigned_subject = SmAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->get();
            $mark_store = DB::table('sm_mark_stores')->where([['class_id', $class_id], ['section_id', $section_id], ['exam_term_id', $exam_id], ['student_id', $student_id]])->first();
            $subject_marks = [];
            $subject_gpas = [];
            foreach ($assigned_subject as $subject) {
                $subject_mark = DB::table('sm_mark_stores')->where([['class_id', $class_id], ['section_id', $section_id], ['exam_term_id', $exam_id], ['student_id', $student_id], ['subject_id', $subject->subject_id]])->first();
                $custom_result = new self;  // correct

                $subject_gpa = $custom_result->getGpa($subject_mark->total_marks);
                // return $subject_mark;
                $subject_marks[$subject->subject_id][0] = $subject_mark->total_marks;
                $subject_marks[$subject->subject_id][1] = $subject_gpa;
                $subject_gpas[$subject->subject_id] = $subject_gpa;
            }

            $total_gpa = array_sum($subject_gpas);

            return $total_gpa / $subject_count;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getSubjectGpa($class_id, $section_id, $exam_id, $student_id, $subject)
    {
        try {
            $subject_marks = [];
            $subject_mark = DB::table('sm_mark_stores')->where('student_id', $student_id)
                ->where('exam_term_id', '=', $exam_id)->first();

            $custom_result = new self;
            $subject_gpa = $custom_result->getGpa($subject_mark->total_marks);

            $subject_marks[$subject][0] = $subject_mark->total_marks;
            $subject_marks[$subject][1] = $subject_gpa;

            // return $subject_mark->total_marks;
            return $subject_marks;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getFinalResult($exam_id, $class_id, $section_id, $student_id, $percentage)
    {
        try {
            $system_setting = SmGeneralSettings::where('id', auth()->user()->school_id)->first();
            $system_setting = $system_setting->session_id;
            $custom_result_setup = self::where('academic_year', $system_setting)->first();

            $assigned_subject = SmAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->get();

            $all_subjects_gpa = [];
            foreach ($assigned_subject as $subject) {
                $custom_result = new self;
                $subject_gpa = $custom_result->getSubjectGpa($exam_id, $class_id, $section_id, $student_id, $subject->subject_id);
                $all_subjects_gpa[] = $subject_gpa[$subject->subject_id][1];
            }

            $percentage = $custom_result_setup->$percentage;
            $term_gpa = array_sum($all_subjects_gpa) / $assigned_subject->count();
            $percentage = number_format((float) $percentage, 2, '.', '');

            return ($percentage / 100) * $term_gpa;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function examTypeName()
    {
        return $this->belongsTo(SmExamType::class, 'exam_type_id', 'id');

    }

    public function scopeStatus($query)
    {
        return $query->where('school_id', auth()->user()->school_id)->where('academic_id', getAcademicId());
    }
}
