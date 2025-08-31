<?php

namespace App\Models;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function colors()
    {
        return $this->belongsToMany(Color::class)->where('status', 1)->withPivot(['value']);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SchoolScope);
    }
}
