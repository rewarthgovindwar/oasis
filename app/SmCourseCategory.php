<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmCourseCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function courses()
    {
        return $this->hasMany(SmCourse::class, 'category_id');
    }
}
