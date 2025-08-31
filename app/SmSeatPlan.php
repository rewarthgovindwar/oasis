<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmSeatPlan extends Model
{
    use HasFactory;

    public static function total_student($class, $section)
    {
        try {
            return SmStudent::where('class_id', $class)->where('section_id', $section)->count();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function seatPlanChild()
    {
        return $this->hasMany(SmSeatPlanChild::class, 'seat_plan_id', 'id');
    }

    public function exam()
    {
        return $this->belongsTo(SmExam::class, 'exam_id', 'id');
    }

    public function subject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }
}
