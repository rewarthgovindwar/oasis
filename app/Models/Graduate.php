<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Graduate extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function student()
    {
        return $this->belongsTo(\App\SmStudent::class, 'student_id', 'id')->withDefault();
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

    public function unAlumni()
    {
        return $this->hasOne(\Modules\Alumni\Entities\Alumni::class, 'un_graduate_id');
    }

    public function graduateStudentDetail()
    {
        return $this->hasOne('Modules\Alumni\Entities\GraduateStudentDetail', 'graduate_id');
    }

    // sm_record_table
    public function section()
    {
        return $this->belongsTo(\App\SmSection::class, 'section_id', 'id')->withDefault();
    }

    public function smClass()
    {
        return $this->belongsTo(\App\SmClass::class, 'class_id', 'id')->withDefault();
    }

    protected static function newFactory()
    {
        return \Modules\Alumni\Database\factories\GraduateFactory::new();
    }
}
