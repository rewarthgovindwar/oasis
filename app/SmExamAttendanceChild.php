<?php

namespace App;

use App\Models\StudentRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmExamAttendanceChild extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id')->with('class', 'section');
    }

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id', 'id');
    }
    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }
}
