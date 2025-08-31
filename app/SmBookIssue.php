<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmBookIssue extends Model
{
    use HasFactory;

    public function books()
    {
        return $this->belongsTo(SmBook::class, 'book_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo(SmLibraryMember::class, 'member_id', 'student_staff_id');
    }

    public function user()
    {
        return $this->belongsTo(Models\User::class, 'member_id', 'id');
    }

    public function getMemberDetailsAttribute()
    {
        $full_name = '';
        if ($this->member) {
            $full_name = $this->member->studentDetails->full_name;
        } elseif ($this->member && $this->member->member_type == 3) {
            $full_name = $this->member->parentsDetails->guardians_name;
        } else {
            $full_name = @$this->member->staffDetails->full_name;
        }

        return $full_name;
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
