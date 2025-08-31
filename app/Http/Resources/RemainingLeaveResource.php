<?php

namespace App\Http\Resources;

use App\SmLeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemainingLeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if(auth()->user()->role_id == 2)
        {
           $approved_leaves = SmLeaveRequest::approvedLeave($this->id); 
        }else{
               
           $approved_leaves = SmLeaveRequest::approvedLeaveStudent($this->id,$this->user_id);
        }
        
        $remaining_days = $this->days - $approved_leaves;

        return [
            'id' => (int) $this->id,
            'leave_type' => (string) @$this->leaveType->type,
            'remaining_days' => (int) $remaining_days >= 0 ? $remaining_days : 0,
        ];
    }
}
