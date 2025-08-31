<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\OnlineExam\Entities\InfixStudentTakeOnlineExam;

class SmOnlineExam extends Model
{
    use HasFactory;

    public static function obtainedMarks($exam_id, $student_id, $record_id = null)
    {

        try {
            if (moduleStatusCheck('OnlineExam') == true) {
                return InfixStudentTakeOnlineExam::select('status', 'student_done', 'total_marks')
                    ->where('online_exam_id', $exam_id)->where('student_id', $student_id)
                    ->where('student_record_id', $record_id)
                    ->first();
            }

            return SmStudentTakeOnlineExam::select('status', 'total_marks')
                ->where('online_exam_id', $exam_id)
                ->where('student_id', $student_id)
                ->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'id', 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function section()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(SmSection::class, 'un_section_id', 'id');
        }

        return $this->belongsTo(SmSection::class, 'section_id', 'id');

    }

    public function subject()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(\Modules\University\Entities\UnSubject::class, 'un_subject_id', 'id');
        }

        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');

    }

    public function unSemesterLabel()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemesterLabel::class, 'un_semester_label_id', 'id')->withDefault();
    }

    public function assignQuestions()
    {
        return $this->hasMany(SmOnlineExamQuestionAssign::class, 'online_exam_id', 'id')->withOutGlobalScopes();
    }

    public function studentAttend()
    {
        return $this->hasOne(SmStudentTakeOnlineExam::class, 'online_exam_id', 'id');
    }

    public function smStudentTakeOnlineExam()
    {
        return $this->hasMany(SmStudentTakeOnlineExam::class, 'online_exam_id', 'id');
    }

    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
