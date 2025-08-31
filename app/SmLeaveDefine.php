<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmLeaveDefine extends Model
{
    use HasFactory;

    public function role()
    {
        return $this->belongsTo(\Modules\RolePermission\Entities\InfixRole::class, 'role_id', 'id');
    }

    public function leaveType()
    {
        return $this->belongsTo(SmLeaveType::class, 'type_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(SmLeaveRequest::class, 'leave_define_id')->where('approve_status', '=', 'A');
    }

    public function getremainingDaysAttribute()
    {
        $diff_in_days = 0;
        foreach ($this->leaveRequests as $leaveRequest) {
            $to = Carbon::parse($leaveRequest->leave_from);
            $from = Carbon::parse($leaveRequest->leave_to);
            $diff_in_days = $to->diffInDays($from) + 1;
        }

        return $diff_in_days;
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
