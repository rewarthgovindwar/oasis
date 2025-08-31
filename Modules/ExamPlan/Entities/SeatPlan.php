<?php

namespace Modules\ExamPlan\Entities;

use App\Models\StudentRecord;
use App\SmAcademicYear;
use App\SmExamType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatPlan extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id', 'id');
    }

    public function examType()
    {
        return $this->belongsTo(SmExamType::class, 'exam_type_id', 'id');
    }

    public function academicYear()
    {
        return $this->belongsTo(SmAcademicYear::class, 'academic_id', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\ExamPlan\Database\factories\SeatPlanFactory::new();
    }
}
