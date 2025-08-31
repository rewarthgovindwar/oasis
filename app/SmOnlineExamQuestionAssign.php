<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmOnlineExamQuestionAssign extends Model
{
    use HasFactory;

    public function questionBank()
    {
        return $this->belongsTo(SmQuestionBank::class, 'question_bank_id', 'id')->withOutGlobalScopes();
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
}
