<?php

namespace Modules\Fees\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FmFeesWeaver extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\Fees\Database\factories\FmFeesWeaverFactory::new();
    }
}
