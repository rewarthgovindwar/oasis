<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmStudentTakeOnlineExam extends Model
{
    use HasFactory;

    public static function submittedAnswer($exam_id, $s_id)
    {
        try {
            return self::where('online_exam_id', $exam_id)->where('student_id', $s_id)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function answeredQuestions()
    {
        return $this->hasMany(SmStudentTakeOnlineExamQuestion::class, 'take_online_exam_id', 'id');
    }

    public function onlineExam()
    {
        return $this->belongsTo(SmOnlineExam::class, 'online_exam_id', 'id');
    }
}
