<?php

namespace App;

use Exception;
use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Modules\University\Entities\UnSubject;
use Modules\University\Entities\UnSemesterLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmMarkStore extends Model
{
    use HasFactory;

    public static function get_mark_by_part($student_id, $exam_id, $class_id, $section_id, $subject_id, $exam_setup_id, $record_id)
    {

        try {
            $getMark = self::where([
                ['student_id', $student_id],
                ['exam_term_id', $exam_id],
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['exam_setup_id', $exam_setup_id],
                ['student_record_id', $record_id],
                ['subject_id', $subject_id],
            ])->first();
            if (! empty($getMark)) {
                return $getMark->total_marks;
            }

            return '0';
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function un_get_mark_by_part($student_id, $request, $exam_id, $subject_id, $exam_setup_id, $record_id)
    {
        try {
            $SmMarkStore = self::query();
            $getMark = universityFilter($SmMarkStore, $request)
                ->where([
                    ['student_id', $student_id],
                    ['exam_term_id', $exam_id],
                    ['exam_setup_id', $exam_setup_id],
                    ['student_record_id', $record_id],
                    ['un_subject_id', $subject_id],
                ])->first();

            if (! empty($getMark)) {
                return $getMark->total_marks;
            }

            return '0';
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function is_absent_check($student_id, $exam_id, $class_id, $section_id, $subject_id, $record_id)
    {

        try {
            $getMark = self::where([
                ['student_id', $student_id],
                ['exam_term_id', $exam_id],
                ['class_id', $class_id],
                ['student_record_id', $record_id],
                ['section_id', $section_id],
                ['subject_id', $subject_id],
            ])->first();
            if (! empty($getMark)) {
                return $getMark->is_absent;
            }

            return '0';
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function un_is_absent_check($student_id, $exam_id, $request, $subject_id, $record_id)
    {
        try {
            $SmMarkStore = self::query();
            $getMark = universityFilter($SmMarkStore, $request)
                ->where([
                    ['student_id', $student_id],
                    ['exam_term_id', $exam_id],
                    ['student_record_id', $record_id],
                    ['subject_id', $subject_id],
                ])->first();
            if (!empty($getMark)) {
                return $getMark->is_absent;
            }

            return '0';
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function teacher_remarks($student_id, $exam_id, $class_id, $section_id, $subject_id, $record_id)
    {

        $getMark = self::where([
            ['student_id', $student_id],
            ['exam_term_id', $exam_id],
            ['class_id', $class_id],
            ['section_id', $section_id],
            ['student_record_id', $record_id],
            ['subject_id', $subject_id],
        ])->first();

        if (!empty($getMark)) {
            return $getMark->teacher_remarks;
        }

        return '';
    }

    public static function un_teacher_remarks($student_id, $exam_id, $request, $subject_id, $record_id)
    {

        $builder = self::query();
        $getMark = universityFilter($builder, $request)
            ->where([
                ['student_id', $student_id],
                ['exam_term_id', $exam_id],
                ['student_record_id', $record_id],
                ['un_subject_id', $subject_id],
            ])->first();

        if (!empty($getMark)) {
            return $getMark->teacher_remarks;
        }

        return '';
    }

    /**
     * @return mixed[]
     */
    public static function allMarksArray($exam_id, $class_id, $section_id, $subject_id): array
    {
        $all_student_marks = [];

        $marks = SmResultStore::where('class_id', $class_id)->where('section_id', $section_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam_id)->get();

        foreach ($marks as $mark) {
            $all_student_marks[] = $mark->total_marks;
        }

        return $all_student_marks;

    }

    public function class()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(UnSemesterLabel::class, 'un_semester_label_id', 'id');
        }

        return $this->belongsTo(SmClass::class, 'class_id', 'id');

    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function subjectName()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(UnSubject::class, 'un_subject_id', 'id');
        }

        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');

    }


    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
}
