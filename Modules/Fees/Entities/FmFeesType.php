<?php

namespace Modules\Fees\Entities;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FmFeesType extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'fees_group_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
    ];

    protected $fillable = [];

    public function fessGroup()
    {
        return $this->belongsTo(FmFeesGroup::class, 'fees_group_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }

    protected static function newFactory()
    {
        return \Modules\Fees\Database\factories\FmFeesTypeFactory::new();
    }
}
