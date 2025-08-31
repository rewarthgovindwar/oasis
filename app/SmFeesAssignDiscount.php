<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmFeesAssignDiscount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function feesDiscount()
    {
        return $this->belongsTo(SmFeesDiscount::class, 'fees_discount_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
