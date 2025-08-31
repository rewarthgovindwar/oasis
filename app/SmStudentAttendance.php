<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmStudentAttendance extends Model
{
    use HasFactory;

    protected $table = 'sm_student_attendances';

    protected $guarded = [];

    protected $casts = [
        'attendance_type' => 'string',
        'attendance_date' => 'string',
    ];

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function scopemonthAttendances($query, $month)
    {
        return $query->whereMonth('attendance_date', $month);
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
}
