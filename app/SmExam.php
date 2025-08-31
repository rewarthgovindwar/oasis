<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SmExam extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function getMarkDistributions($ex_id, $class_id, $section_id, $subject_id)
    {
        try {
            return SmExamSetup::where([
                ['exam_term_id', $ex_id],
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['subject_id', $subject_id],
            ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getMarkREgistered($ex_id, $class_id, $section_id, $subject_id)
    {
        try {
            return SmMarkStore::where([
                ['exam_term_id', $ex_id],
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['subject_id', $subject_id],
            ])->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function globalClass()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function getClassName()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function GetSectionName()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function GetSubjectName()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }

    public function GetExamTitle()
    {
        return $this->belongsTo(SmExamType::class, 'exam_type_id', 'id');
    }

    public function GetGlobalExamTitle()
    {
        return $this->belongsTo(SmExamType::class, 'exam_type_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(AcademicSchoolScope::class);
    }

    public function subject()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(\Modules\University\Entities\UnSubject::class, 'un_subject_id', 'id');
        }

        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }

    public function globalSubject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function globalSection()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function GetExamSetup()
    {
        return $this->hasMany(SmExamSetup::class, 'exam_id', 'id');
    }

    public function examType()
    {
        return $this->hasOne(SmExamType::class, 'id', 'exam_type_id');
    }

    public function markRegistered()
    {
        return $this->hasOne(SmMarkStore::class, 'exam_term_id', 'exam_type_id')
            ->where('class_id', $this->class_id)->where('section_id', $this->section_id);
    }

    public function marks()
    {
        return $this->hasMany(SmExamSetup::class, 'exam_id', 'id');
    }

    public function markDistributions()
    {
        return $this->marks();
    }

    public function markStore()
    {
        return $this->hasOne(SmMarkStore::class, 'exam_term_id', 'exam_type_id')
            ->where('class_id', $this->class_id)->where('section_id', $this->section_id)->where('subject_id', $this->subject_id)
            ->where('school_id', Auth::user()->school_id);
    }

    public function sessionDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSession::class, 'un_session_id', 'id')->withDefault();
    }

    public function semesterDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemester::class, 'un_semester_id', 'id')->withDefault();
    }

    public function academicYearDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnAcademicYear::class, 'un_academic_id', 'id')->withDefault();
    }

    public function departmentDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnDepartment::class, 'un_department_id', 'id')->withDefault();
    }

    public function facultyDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnFaculty::class, 'un_faculty_id', 'id')->withDefault();
    }

    public function subjectDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSubject::class, 'un_subject_id', 'id')->withDefault();
    }

    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
        static::addGlobalScope(new GlobalAcademicScope);
    }
}
