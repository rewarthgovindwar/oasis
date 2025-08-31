<?php

namespace App;

use App\Models\DirectFeesInstallment;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmFeesMaster extends Model
{
    use HasFactory;

    protected $fillable = ['fees_group_id', 'fees_type_id', 'date', 'amount', 'un_semester_label_id', 'academic_id', 'school_id', 'un_subject_id', 'un_academic_id'];

    public function feesTypes()
    {
        return $this->belongsTo(SmFeesType::class, 'fees_type_id');
    }

    public function feesType()
    {
        return $this->belongsTo(SmFeesType::class, 'fees_type_id', 'id');
    }

    public function feesGroups()
    {
        return $this->belongsTo(SmFeesGroup::class, 'fees_group_id', 'id');
    }

    public function feesTypeIds()
    {
        return $this->hasMany(self::class, 'fees_group_id', 'fees_group_id');
    }

    public function installments()
    {
        return $this->hasMany(DirectFeesInstallment::class, 'fees_master_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
