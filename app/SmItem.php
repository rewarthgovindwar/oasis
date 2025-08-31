<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmItem extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(SmItemCategory::class, 'item_category_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
}
