<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmLeaveType extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
    ];

    public function leaveDefines()
    {
        return $this->hasMany(SmLeaveDefine::class, 'type_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
}
