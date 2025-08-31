<?php

namespace App;

use App\Models\StudentRecord;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SmResultStore extends Model
{
    use HasFactory;

    public static function remarks($gpa)
    {
        $school_id = 1;
        if (Auth::check()) {
            $school_id = Auth::user()->school_id;
        } elseif (app()->bound('school')) {
            $school_id = app('school')->id;
        }

        try {
            return SmMarksGrade::where([
                ['from', '<=', $gpa],
                ['up', '>=', $gpa]]
            )
                ->where('school_id', $school_id)
                ->where('academic_id', getAcademicId())
                ->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function GetResultBySubjectId($class_id, $section_id, $subject_id, $exam_id, $student_id)
    {

        try {
            return SmMarkStore::withOutGlobalScopes()->where([
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_id],
                ['student_record_id', $student_id],
                ['subject_id', $subject_id],
            ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function un_GetResultBySubjectId($subject_id, $exam_id, $student_id, $request)
    {

        try {
            $SmMarkStore = SmMarkStore::query();

            return universityFilter($SmMarkStore, $request)
                ->where([
                    ['exam_term_id', $exam_id],
                    ['student_id', $student_id],
                    ['un_subject_id', $subject_id],
                ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function GetFinalResultBySubjectId($class_id, $section_id, $subject_id, $exam_id, $student_id)
    {

        try {
            return self::where([
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['exam_type_id', $exam_id],
                ['student_record_id', $student_id],
                ['subject_id', $subject_id],
            ])->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function un_GetFinalResultBySubjectId($subject_id, $exam_id, $student_id, $request)
    {
        try {
            $SmResultStore = self::query();

            return universityFilter($SmResultStore, $request)
                ->where([
                    ['exam_type_id', $exam_id],
                    ['student_id', $student_id],
                    ['un_subject_id', $subject_id],
                ])->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function termBaseMark($class_id, $section_id, $subject_id, $exam_id, $student_id)
    {
        return self::where([
            ['class_id', $class_id],
            ['section_id', $section_id],
            ['exam_type_id', $exam_id],
            ['student_record_id', $student_id],
            ['subject_id', $subject_id],
        ])
            ->distinct('exam_type_id')
            ->sum('total_gpa_point');
    }

    public static function un_termBaseMark($subject_id, $exam_id, $student_id, $request)
    {

        $builder = self::query();

        return universityFilter($builder, $request)
            ->where([
                ['exam_type_id', $exam_id],
                ['student_id', $student_id],
                ['un_subject_id', $subject_id],
            ])
            ->distinct('exam_type_id')
            ->sum('total_gpa_point');
    }

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function exam()
    {
        return $this->belongsTo(SmExamType::class, 'exam_type_id');
    }

    public function subject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id', 'id');
    }

    public function studentRecords()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id', 'id');
    }

    public function unSubjectDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSubject::class, 'un_subject_id', 'id');
    }
    
    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }
}
