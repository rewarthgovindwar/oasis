<?php

namespace App;

use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmSection extends Model
{
    //
    use HasFactory;

    public function students()
    {
        return $this->hasMany(SmStudent::class, 'section_id', 'id');
    }

    public function unAcademic()
    {
        return $this->belongsTo(\Modules\University\Entities\UnAcademicYear::class, 'un_academic_id', 'id')->withDefault();
    }

    protected static function boot()
    {
        parent::boot();
        // static::addGlobalScope(new GlobalAcademicScope);
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
