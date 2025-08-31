<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmMarksRegisterChild extends Model
{
    use HasFactory;

    public function subject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }
}
