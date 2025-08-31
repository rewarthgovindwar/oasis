<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmExamAttendance extends Model
{
    use HasFactory;

    public function examAttendanceChild()
    {
        return $this->hasMany(SmExamAttendanceChild::class, 'exam_attendance_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function subject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }

    // public function scopesClassSection($query){
    //     return $query->where('class_id',request()->class_id)->where('section_id',request()->section_id)->where('subject_id',request()->subject_id);
    // }
    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }
}
