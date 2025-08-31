<?php

namespace App;

use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmClassSection extends Model
{
    use HasFactory;

    public function className()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id')->withDefault();
    }

    public function sectionName()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id')->withDefault();
    }

    public function sectionNameSaas()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    public function globalSectionName()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    public function globalClassName()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->withDefault();
    }

    public function students()
    {
        return $this->hasMany(SmStudent::class, 'section_id', 'section_id');
    }

    public function sectionWithoutGlobal()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id')->withoutGlobalScopes()->withDefault();
    }


    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new GlobalAcademicScope);
        // static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
