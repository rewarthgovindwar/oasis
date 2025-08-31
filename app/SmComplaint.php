<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmComplaint extends Model
{
    use HasFactory;

    public function complaintType()
    {
        return $this->belongsTo(SmSetupAdmin::class, 'complaint_type', 'id');
    }

    public function complaintSource()
    {
        return $this->belongsTo(SmSetupAdmin::class, 'complaint_source', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
