<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmStaffAttendence extends Model
{
    use HasFactory;

    protected $table = 'sm_staff_attendences';

    protected $guarded = [];

    public function StaffInfo()
    {
        return $this->belongsTo(SmStaff::class, 'staff_id', 'id');
    }
}
