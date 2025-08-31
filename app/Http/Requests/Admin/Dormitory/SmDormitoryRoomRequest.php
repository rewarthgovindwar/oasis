<?php

namespace App\Http\Requests\Admin\Dormitory;

use Illuminate\Foundation\Http\FormRequest;

class SmDormitoryRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:100',
            'dormitory' => 'required',
            'room_type' => 'required',
            'number_of_bed' => 'required|max:2',
            'cost_per_bed' => 'required|max:11',
            'description' => 'sometimes|nullable|max:200',
        ];
    }
}
