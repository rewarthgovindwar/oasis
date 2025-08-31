<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmTemporaryMeritlist extends Model
{
    use HasFactory;

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function studentinfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function exam()
    {
        return $this->belongsTo(SmExam::class, 'exam_id', 'id');
    }
}
