<?php

namespace App;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmLeaveRequest extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'apply_date' => 'string',
        'leave_from' => 'string',
        'leave_to' => 'string',
        'reason' => 'string',
        'file' => 'string',
        'leave_define_id' => 'integer',
    ];

    public static function approvedLeave($type_id)
    {

        try {
            $user = Auth::user();
            $leaves = self::where('role_id', $user->role_id)->where('staff_id', $user->id)
                ->where('leave_define_id', $type_id)->where('approve_status', 'A')->get();

            $approved_days = 0;
            foreach ($leaves as $leaf) {
                $start = strtotime($leaf->leave_from);
                $end = strtotime($leaf->leave_to);
                $days_between = ceil(abs($end - $start) / 86400);
                $days = $days_between + 1;
                $approved_days += $days;
            }

            return $approved_days;
        } catch (Exception $exception) {
            return [];
        }
    }
    
    public static function approvedLeaveStudent($type_id,$user_id)
    {

        try {
            $user = User::find($user_id);
            $leaves = self::where('role_id', $user->role_id)->where('staff_id', $user->id)
                ->where('leave_define_id', $type_id)->where('approve_status', 'A')->get();

            $approved_days = 0;
            foreach ($leaves as $leaf) {
                $start = strtotime($leaf->leave_from);
                $end = strtotime($leaf->leave_to);
                $days_between = ceil(abs($end - $start) / 86400);
                $days = $days_between + 1;
                $approved_days += $days;
            }

            return $approved_days;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function approvedLeaveModal($type_id, $role_id, $staff_id)
    {

        try {
            $leaves = self::where('role_id', $role_id)->where('staff_id', $staff_id)->where('leave_define_id', $type_id)->where('approve_status', 'A')->get();
            $approved_days = 0;
            foreach ($leaves as $leaf) {
                $start = strtotime($leaf->leave_from);
                $end = strtotime($leaf->leave_to);
                $days_between = ceil(abs($end - $start) / 86400);
                $days = $days_between + 1;
                $approved_days += $days;
            }

            return $approved_days;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function leaveType()
    {
        return $this->belongsTo(SmLeaveType::class, 'type_id');
    }

    public function leaveDefine()
    {
        return $this->belongsTo(SmLeaveDefine::class, 'leave_define_id', 'id');
    }

    public function staffs()
    {
        return $this->belongsTo(SmStaff::class, 'staff_id', 'user_id');
    }

    public function student()
    {
        return $this->belongsTo(SmStudent::class, 'staff_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(Models\User::class, 'staff_id', 'id');
    }

    public function getRemainingDaysAttribute(): float
    {
        $to = Carbon::parse($this->leave_from);
        $from = Carbon::parse($this->leave_to);

        return $to->diffInDays($from);
    }
}
