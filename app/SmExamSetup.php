<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmExamSetup extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function subjectDetails()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }

    public function unSubject()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSubject::class, 'un_subject_id', 'id');
    }
    public function shift(){
        return $this->belongsTo(\App\Models\Shift::class, 'shift_id', 'id');
    }
}
