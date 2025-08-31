<?php

namespace App;

use App\Models\StudentRecord;
use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\BehaviourRecords\Entities\AssignIncident;

class SmClass extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'class_name' => 'string',
    ];

    public function classSection()
    {
        return $this->hasMany(SmClassSection::class, 'class_id')->with('sectionName');

    }

    public function classSectionAll()
    {
        return $this->belongsToMany(SmSection::class, 'sm_class_sections', 'class_id', 'section_id');
    }

    public function sectionName()
    {
        return $this->belongsTo(SmSection::class, 'section_id');
    }

    public function sections()
    {
        return $this->hasMany(SmSection::class, 'id', 'section_id');
    }

    public function records()
    {
        return $this->hasMany(StudentRecord::class, 'class_id', 'id')->where('is_promote', 0)->whereHas('student');
    }

    public function allIncident()
    {
        return $this->hasManyThrough(AssignIncident::class, StudentRecord::class, 'class_id', 'record_id', 'id', 'id');
    }

    public function classSections()
    {
        return $this->hasMany(SmClassSection::class, 'class_id', 'id');
    }

    public function groupclassSections()
    {
        return $this->hasMany(SmClassSection::class, 'class_id', 'id')->with('sectionName');
    }

    public function globalGroupclassSections()
    {
        return $this->hasMany(SmClassSection::class, 'class_id', 'id')->distinct(['class_id', 'section_id'])->balScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->with('sectionName');
    }

    public function students()
    {
        return $this->hasMany(SmStudent::class, 'user_id', 'id');
    }

    public function subjects()
    {
        return $this->hasMany(SmAssignSubject::class, 'class_id')->withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function routineUpdates()
    {
        return $this->hasMany(SmClassRoutineUpdate::class, 'class_id')->where('active_status', 1);
    }

    public function academic()
    {
        return $this->belongsTo(SmAcademicYear::class, 'academic_id', 'id')->withDefault();
    }

    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
        static::addGlobalScope(new GlobalAcademicScope);
    }
}
