<?php

namespace App;

use App\Models\TeacherEvaluation;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SmStaff extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'full_name' => 'string',
        'user_id' => 'integer',
    ];

    protected $guarded = ['id'];

    protected $table = 'sm_staffs';

    public function roles()
    {
        return $this->belongsTo(\Modules\RolePermission\Entities\InfixRole::class, 'role_id', 'id')->withDefault();
    }

    public function departments()
    {
        return $this->belongsTo(SmHumanDepartment::class, 'department_id', 'id')->withDefault();
    }

    public function designations()
    {
        return $this->belongsTo(SmDesignation::class, 'designation_id', 'id')->withDefault();
    }

    public function genders()
    {
        return $this->belongsTo(SmBaseSetup::class, 'gender_id', 'id')->withDefault();
    }

    public function staff_user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault();
    }

    public function attendances()
    {
        return $this->hasMany(SmStaffAttendence::class, 'staff_id')->where('school_id', auth()->user()->school_id);
    }

    public function getAttendanceType($month)
    {
        return $this->attendances()->whereMonth('attendence_date', $month)->get();
    }

    public function classes()
    {
        return $this->hasMany(SmAssignSubject::class, 'teacher_id', 'id')
            ->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.class_id')
            ->select('sm_classes.id', 'class_name')
            ->distinct();
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('school_id', Auth::user()->school_id);
    }

    public function DateWiseStaffAttendance()
    {
        return $this->hasOne(SmStaffAttendence::class, 'staff_id', 'id')->where('attendence_date', date('Y-m-d', strtotime(request()->attendance_date)));
    }

    public function DateWiseStaffAttendanceReport()
    {
        return $this->hasOne(SmStaffAttendence::class, 'staff_id', 'id')->where('attendence_date', 'like', request()->year.'-'.request()->month.'%');
    }

    public function payrollStatus()
    {

        return $this->hasOne(SmHrPayrollGenerate::class, 'staff_id', 'id')
            ->where('payroll_month', request()->payroll_month)
            ->where('payroll_year', request()->payroll_year);
    }

    public function previousRole()
    {
        return $this->belongsTo(\Modules\RolePermission\Entities\InfixRole::class, 'previous_role_id', 'id')->withDefault();
    }

    public function scopeWhereRole($query, $role_id)
    {
        return $query->where(function ($q) use ($role_id): void {
            $q->where('role_id', $role_id)->orWhere('previous_role_id', $role_id);
        });
    }

    public function scopeWhereTeacher($query)
    {
        return $query->where(function ($q): void {
            $q->where('role_id', 4)->orWhere('previous_role_id', 4);
        });
    }

    public function teacherEvaluation()
    {
        return $this->hasMany(TeacherEvaluation::class, 'record_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
