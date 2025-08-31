<?php

namespace Modules\Fees\Entities;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FmFeesGroup extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
    ];

    public function feesTypes()
    {
        return $this->hasMany(FmFeesType::class, 'fees_group_id');
    }

    public function feesTypeNames()
    {
        return $this->hasMany(FmFeesType::class, 'fees_group_id')->select(['name']);
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }

    protected static function newFactory()
    {
        return \Modules\Fees\Database\factories\FmFeesGroupFactory::new();
    }
}
