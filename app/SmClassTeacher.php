<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmClassTeacher extends Model
{
    use HasFactory;

    public function teacher()
    {
        return $this->belongsTo(SmStaff::class, 'teacher_id', 'id');
    }

    public function teacherClass()
    {
        return $this->belongsTo(SmAssignClassTeacher::class, 'assign_class_teacher_id', 'id');
    }
}
