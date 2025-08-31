<?php

namespace Modules\Lesson\Entities;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Shift;

class LessonPlanner extends Model
{
    protected $fillable = [];

    public function class()
    {
        return $this->belongsTo(\App\SmClass::class, 'class_id')->withDefault();
    }

    public function sectionName()
    {
        return $this->belongsTo(\App\SmSection::class, 'section_id')->withDefault();
    }

    public function subject()
    {
        return $this->belongsTo(\App\SmSubject::class, 'subject_id')->withDefault();
    }

    public function shift(){
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function lessonName()
    {
        return $this->belongsTo(SmLesson::class, 'lesson_detail_id')->withDefault();
    }

    public function topics()
    {
        return $this->hasMany(LessonPlanTopic::class, 'lesson_planner_id');
    }

    public function topicName()
    {
        return $this->belongsTo(SmLessonTopicDetail::class, 'topic_detail_id')->withDefault();
    }

    public function teacherName()
    {
        return $this->belongsTo(\App\SmStaff::class, 'teacher_id')->withDefault();
    }

    // public function scopeLessonPlanner($query, $teacher, $class, $section, $subject)
    // {
    //     return $query->where('teacher_id', $teacher)
    //         ->where('class_id', $class)
    //         ->where('section_id', $section)
    //         ->where('subject_id', $subject)
    //         ->where('academic_id', getAcademicId())
    //         ->where('school_id', Auth::user()->school_id)
    //         ->where('active_status', 1);
    // }

    public function scopeLessonPlanner($query, $teacher, $class, $section, $subject, $shift = null)
    {
        $query->where('teacher_id', $teacher)
            ->where('class_id', $class)
            ->where('section_id', $section)
            ->where('subject_id', $subject)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->where('active_status', 1);
        if ($shift !== null && $shift !== '' && shiftEnable()) {
            $query->where('shift_id', $shift);
        }

        return $query;
    }

    public function unSession()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSession::class, 'un_session_id', 'id')->withDefault();
    }

    public function unFaculty()
    {
        return $this->belongsTo(\Modules\University\Entities\UnFaculty::class, 'un_faculty_id', 'id')->withDefault();
    }

    public function unDepartment()
    {
        return $this->belongsTo(\Modules\University\Entities\UnDepartment::class, 'un_department_id', 'id')->withDefault();
    }

    public function unAcademic()
    {
        return $this->belongsTo(\Modules\University\Entities\UnAcademicYear::class, 'un_academic_id', 'id')->withDefault();
    }

    public function unSemester()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemester::class, 'un_semester_id', 'id')->withDefault();
    }

    public function unSemesterLabel()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemesterLabel::class, 'un_semester_label_id', 'id')->withDefault();
    }

    public function unSubject()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSubject::class, 'un_subject_id', 'id')->withDefault();
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
