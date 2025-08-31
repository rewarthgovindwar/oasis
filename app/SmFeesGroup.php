<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmFeesGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'created_by', 'active_status', 'school_id', 'un_semester_label_id', 'un_subject_id', 'un_academic_id'];

    public function feesMasters()
    {
        return $this->hasmany(SmFeesMaster::class, 'fees_group_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AcademicSchoolScope);
    }
}
