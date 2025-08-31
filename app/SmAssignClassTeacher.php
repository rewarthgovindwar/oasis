<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Shift;

class SmAssignClassTeacher extends Model
{
    use HasFactory;

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function classTeachers()
    {
        return $this->hasMany(SmClassTeacher::class, 'assign_class_teacher_id', 'id');
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('school_id', Auth::user()->school_id);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }
}
