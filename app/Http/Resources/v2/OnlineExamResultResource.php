<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnlineExamResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $total_marks = 0;
        foreach ($this->onlineExam->assignQuestions as $assignQuestion) {
            @($total_marks += @$assignQuestion->questionBank->marks);
        }

        $result = $total_marks > 0 ? @$this->total_marks * 100 / @$total_marks : 0;
        $result = @$result >= @$this->onlineExam->percentage ? 'Pass' : 'Fail';

        return [
            'id' => (int) $this->id,
            'title' => (string) $this->onlineExam->title,
            'start_date' => (string) dateConvert(@$this->onlineExam->date),
            'end_date' => (string) dateConvert(date('m/d/Y', strtotime(@$this->onlineExam->end_date_time))),
            'exam_time' => date('h:i A', strtotime(@$this->onlineExam->start_time)),
            'end_time' => date('h:i A', strtotime(@$this->onlineExam->end_time)),
            'total_marks' => (float) @$total_marks,
            'obtained_marks' => (float) $this->total_marks,
            'result' => (string) $result,
        ];
    }
}
