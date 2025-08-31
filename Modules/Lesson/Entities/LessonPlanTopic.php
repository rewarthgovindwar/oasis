<?php

namespace Modules\Lesson\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlanTopic extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function topicName()
    {
        return $this->belongsTo(SmLessonTopicDetail::class, 'topic_id', 'id')->withDefault();
    }

    public function lessonDetail()
    {
        return $this->belongsTo(LessonPlanner::class, 'lesson_planner_id', 'id')->withDefault();
    }

    protected static function newFactory()
    {
        return \Modules\Lesson\Database\factories\LessonPlanTopicFactory::new();
    }
}
