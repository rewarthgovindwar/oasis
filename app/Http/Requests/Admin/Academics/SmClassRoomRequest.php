<?php

namespace App\Http\Requests\Admin\Academics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmClassRoomRequest extends FormRequest
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
            'room_no' => ['required', 'max:100', Rule::unique('sm_class_rooms', 'room_no')->where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)->ignore($this->id)],
            'capacity' => 'required',
        ];
    }
}
