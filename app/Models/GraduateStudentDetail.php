<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraduateStudentDetail extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function graduate()
    {
        return $this->belongsTo(Graduate::class);
    }

    public function student()
    {
        return $this->belongsTo('App\Models\SmStudent', 'student_id', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\Alumni\Database\factories\GraduateStudentDetailFactory::new();
    }
}
