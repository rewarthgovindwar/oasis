<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmAdmissionQuery extends Model
{
    use HasFactory;

    public function className()
    {
        return $this->belongsTo(SmClass::class, 'class', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function referenceSetup()
    {
        return $this->belongsTo(SmSetupAdmin::class, 'reference', 'id');
    }

    public function sourceSetup()
    {
        return $this->belongsTo(SmSetupAdmin::class, 'source', 'id');
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

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AcademicSchoolScope);
    }
}
