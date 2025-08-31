<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmOptionalSubjectAssign extends Model
{
    use HasFactory;

    public static function is_optional_subject($student_id, $subject_id): bool
    {
        try {
            $result = self::where('student_id', $student_id)->where('subject_id', $subject_id)->first();

            return (bool) $result;

        } catch (Exception $exception) {
            return false;
        }
    }

    public function subject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');
    }
}
