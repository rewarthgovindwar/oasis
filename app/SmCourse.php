<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmCourse extends Model
{
    use HasFactory;

    public function courseCategory()
    {
        return $this->belongsTo(SmCourseCategory::class, 'category_id', 'id')->withDefault();
    }
}
