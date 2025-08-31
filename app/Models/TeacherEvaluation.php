<?php

namespace App\Models;

use App\SmAssignSubject;
use App\SmStaff;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherEvaluation extends Model
{
    use HasFactory;

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class, 'record_id', 'id')->withDefault();
    }

    public function staff()
    {
        return $this->belongsTo(SmStaff::class, 'teacher_id', 'id')->withDefault();
    }

    public function assignSubject()
    {
        return $this->belongsTo(SmAssignSubject::class, 'subject_id', 'id')->withDefault();
    }
}
