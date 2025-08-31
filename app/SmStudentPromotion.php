<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmStudentPromotion extends Model
{
    public function student()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'previous_class_id', 'id');
    }
}
