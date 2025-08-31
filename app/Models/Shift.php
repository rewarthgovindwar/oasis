<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    public function getShiftNameAttribute()
    {
        return $this->name;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            cache()->forget('shifts');
        });

        static::updating(function ($model) {
            cache()->forget('shifts');
        });
    }
}
