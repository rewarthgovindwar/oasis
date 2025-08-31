<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmExamSetting extends Model
{
    use HasFactory;

    public function examName()
    {
        return $this->belongsTo(SmExamType::class, 'exam_type', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
