<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmOnlineExamQuestion extends Model
{
    public function multipleOptions()
    {
        return $this->hasMany(SmOnlineExamQuestionMuOption::class, 'online_exam_question_id', 'id');
    }
}
