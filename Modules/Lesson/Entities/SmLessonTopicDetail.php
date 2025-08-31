<?php

namespace Modules\Lesson\Entities;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;

class SmLessonTopicDetail extends Model
{
    protected $fillable = [];

    public function lesson_title()
    {
        return $this->belongsTo(SmLesson::class, 'lesson_id');
    }

    public function lessonPlan()
    {
        return $this->hasMany(LessonPlanTopic::class, 'topic_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
