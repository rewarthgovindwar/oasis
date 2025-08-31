<?php

namespace App\Http\Resources\v2\Class\Student\GMeet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        if ($this->current_status == 'started') {
            $joinUrl = $this->gmeet_url;
            if (auth()->user()->role_id == 1 || auth()->user()->role_id == 4 || auth()->user()->id == $this->created_by) {
                $currentStatus = mb_strtoupper('start');
            } else {
                $currentStatus = mb_strtoupper('join');
            }
        } elseif ($this->current_status == 'waiting') {
            $currentStatus = mb_strtoupper('waiting');
        } else {
            $currentStatus = mb_strtoupper('closed');
        }

        return [
            'meeting_id' => (string) $this->id,
            'topic' => $this->topic,
            'duration' => (string) $this->meeting_duration,
            'start_date' => date('M d', strtotime($this->start_time)),
            'start_time' => date('h:i A', strtotime($this->start_time)),
            'current_status' => $currentStatus,
            'join_url' => @$joinUrl,
        ];
    }
}
