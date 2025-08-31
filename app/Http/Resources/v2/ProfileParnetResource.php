<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileParnetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 
            'fathers_name' => $this->fathers_name, 
            'fathers_mobile' => $this->fathers_mobile, 
            'fathers_occupation' => $this->fathers_occupation,  
            'fathers_photo' => $this->fathers_photo, 
            'mothers_name' => $this->mothers_name,
            'mothers_mobile' => $this->mothers_mobile,
            'mothers_occupation' => $this->mothers_occupation,
            'mothers_photo' => !empty($this->mothers_photo) ? asset($this->mothers_photo):null,
            'guardians_name'=> $this->guardians_name,
            'guardians_mobile' => $this->guardians_mobile,
            'guardians_email'  => $this->guardians_email, 
            'guardians_occupation' => $this->guardians_occupation, 
            'guardians_relation' => $this->guardians_relation,
            'guardians_photo' => !empty($this->guardians_photo) ? asset($this->guardians_photo):null,
        ];
    }
}
