<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'day' => (int) $this->day,
            'start_time' => date('h:i A', strtotime(@$this->start_time)),
            'end_time' => date('h:i A', strtotime(@$this->end_time)),
            'subject_name' => (string) @$this->subjectApi->subject_name,
            'subject_code' => (string) @$this->subjectApi->subject_code,
            'subject_id' => (int) @$this->subjectApi->id,
            'room' => (string) $this->classRoomApi->room_no,
            'teacher' => (string) $this->teacherDetailApi->full_name,
        ];
    }
}
