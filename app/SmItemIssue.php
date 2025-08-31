<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmItemIssue extends Model
{
    use HasFactory;

    public function items()
    {
        return $this->belongsTo(SmItem::class, 'item_id', 'id');
    }

    public function categories()
    {
        return $this->belongsTo(SmItemCategory::class, 'item_category_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
}
