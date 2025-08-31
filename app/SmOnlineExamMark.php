<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmOnlineExamMark extends Model
{
    use HasFactory;

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }
}
