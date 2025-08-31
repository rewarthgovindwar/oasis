<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmFeesType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'fees_group_id', 'un_semester_label_id', 'school_id', 'un_subject_id', 'un_academic_id'];

    public function fessGroup()
    {
        return $this->belongsTo(SmFeesGroup::class, 'fees_group_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
