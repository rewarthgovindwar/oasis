<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SmStudentIdCard extends Model
{
    use HasFactory;

    public static function roleName($id)
    {
        $id_card = self::find($id);
        $arr = [];
        $roles = json_decode($id_card->role_id, true);
        foreach ($roles as $role) {
            $arr[] = $role;
        }

        return Role::whereIn('id', $arr)->get(['id', 'name']);
    }

    public static function studentName($parent_id)
    {
        return SmStudent::where('parent_id', $parent_id)
            ->where('active_status', 1)
            ->where('school_id', Auth::user()->school_id)
            ->get(['full_name', 'student_photo', 'first_name', 'last_name']);
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
