<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmClassRoutine extends Model
{
    use HasFactory;

    public static function teacherId($class_id, $section_id, $subject_id)
    {

        try {
            return SmAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->where('subject_id', $subject_id)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function subject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }
}
