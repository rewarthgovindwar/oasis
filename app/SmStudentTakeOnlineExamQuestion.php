<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmStudentTakeOnlineExamQuestion extends Model
{
    use HasFactory;

    public function questionBank()
    {
        return $this->belongsTo(SmQuestionBank::class, 'question_bank_id', 'id');
    }

    public function takeQuestionMu()
    {
        return $this->hasMany(SmStudentTakeOnlnExQuesOption::class, 'take_online_exam_question_id', 'id');
    }
}
