<?php

namespace Modules\DownloadCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\University\Entities\UnFaculty;
use Modules\University\Entities\UnSession;
use Modules\University\Entities\UnSemester;
use Modules\University\Entities\UnDepartment;
use Modules\University\Entities\UnSemesterLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoUpload extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function class()
    {
        return $this->belongsTo('App\SmClass', 'class_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo('App\SmSection', 'section_id', 'id');
    }

    public function shift()
    {
        return $this->belongsTo('App\Models\Shift', 'shift_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }


    public function unSemesterLabel()
    {
        return $this->belongsTo(UnSemesterLabel::class,'un_semester_label_id');
    }

    public function unSemester()
    {
        return $this->belongsTo(UnSemester::class,'un_semester_id');
    }

    
    public function unFaculty()
    {
        return $this->belongsTo(UnFaculty::class,'un_faculty_id');
    }

    public function unSession()
    {
        return $this->belongsTo(UnSession::class,'un_session_id');
    }

    public function unDepartment()
    {
        return $this->belongsTo(UnDepartment::class,'un_department_id');
    }
}
