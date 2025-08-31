<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmHomeworkStudent extends Model
{
    use HasFactory;

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');

    }

    public function homeworkDetail()
    {
        return $this->belongsTo(SmHomework::class, 'homework_id', 'id');

    }

    protected static function boot()
    {
        parent::boot();
    }
}
