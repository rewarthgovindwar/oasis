<?php

namespace Modules\Fees\Entities;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FmFeesTransactionChield extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function feesType()
    {
        return $this->belongsTo(FmFeesGroup::class, 'fees_type', 'id');
    }

    public function transcationFeesType()
    {
        return $this->belongsTo(FmFeesType::class, 'fees_type', 'id')->withDefault();
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }

    protected static function newFactory()
    {
        return \Modules\Fees\Database\factories\FmFeesTransactionChieldFactory::new();
    }
}
