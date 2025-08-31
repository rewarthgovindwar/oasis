<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonPlanDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = @$this->subject->subject_type == 'T' ? 'Theory' : 'Practical';

        $topic_title = @$this->topicName->topic_title ? [$this->topicName->topic_title] : [];
        $sub_topic_title = @$this->sub_topic ? [$this->sub_topic] : [];

        $topics = @$this->topics ?? [];

        $topicTitle = count($topics) > 0 ? LessonTopicResource::collection($topics) : $topic_title;

        $sub_topic = [];
        if (count($topics) > 0) {
            foreach ($topics as $topic) {
                if ($topic->sub_topic_title) {
                    $sub_topic[] = $topic->sub_topic_title;
                }
            }
        } else {
            $sub_topic = [$sub_topic_title];
        }

        if (! empty($this->id)) {
            return [
                'id' => @$this->id,
                'class_section' => @$this->class->class_name.'('.@$this->sectionName->section_name.')',
                'subject' => @$this->subject->subject_name.'('.@$this->subject->subject_code.') - '.$type,
                'date' => date('d-M-y', strtotime(@$this->lesson_date)),
                'lesson' => (string) @$this->lessonName->lesson_title,
                'topic' => $topicTitle,
                'subtopic' => $sub_topic,
                'lecture_youtube_link' => (string) @$this->lecture_youube_link,
                'document' => (string) @$this->attachment,
                'note' => (string) @$this->note,
                'status' => @$this->completed_status == 'completed',
            ];
        }

        return [];

    }
}
