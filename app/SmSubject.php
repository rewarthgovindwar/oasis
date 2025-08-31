<?php

namespace App;

use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmSubject extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'subject_name' => 'string',
        'subject_code' => 'string',
        'subject_type' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new GlobalAcademicScope);
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }

    //

}
