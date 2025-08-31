<?php

namespace App;

use App\Models\Shift;
use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmTeacherUploadContent extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'content_title' => 'string',
        'upload_date' => 'string',
        'content_type' => 'string',
        'upload_file' => 'string',
        'description' => 'string',
        'available_for_admin' => 'integer',
        'available_for_all_classes' => 'integer',
        'class' => 'integer',
        'section' => 'integer',
    ];

    public function contentTypes()
    {
        return $this->belongsTo(SmContentType::class, 'content_type', 'id');
    }

    public function roles()
    {
        return $this->belongsTo(\Modules\RolePermission\Entities\InfixRole::class, 'available_for', 'id');
    }

    public function classes()
    {
        return $this->belongsTo(SmClass::class, 'class', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function globalClasses()
    {
        return $this->belongsTo(SmClass::class, 'class', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class);
    }

    public function sections()
    {
        return $this->belongsTo(SmSection::class, 'section', 'id');
    }

    public function globalSections()
    {
        return $this->belongsTo(SmSection::class, 'section', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function unSession()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSession::class, 'un_session_id', 'id')->withDefault();
    }

    public function unSection()
    {
        return $this->belongsTo(SmSection::class, 'un_section_id', 'id')->withDefault();
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

    public function semester()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemester::class, 'un_semester_id', 'id')->withDefault();
    }

    public function semesterLabel()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemesterLabel::class, 'un_semester_label_id', 'id')->withDefault();
    }

    public function scopeWhereNullLms($query)
    {
        return $query->whereNull('course_id')->whereNull('chapter_id')->whereNull('lesson_id');
    }

    public function lesson()
    {
        return $this->belongsTo(\Modules\Lms\Entities\CourseLesson::class, 'lesson_id', 'id')->withDefault();
    }

    public function course()
    {
        return $this->belongsTo(\Modules\Lms\Entities\Course::class, 'course_id', 'id')->withDefault();
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new GlobalAcademicScope);
    }
}
