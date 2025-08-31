<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmItemSellChild extends Model
{
    use HasFactory;

    public function items()
    {
        return $this->belongsTo(SmItem::class, 'item_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
}
