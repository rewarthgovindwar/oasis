<?php

namespace App\Http\Requests\Admin\Academics;

use Illuminate\Foundation\Http\FormRequest;

class SmExamTimeRequest extends FormRequest
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
            'period' => 'required|max:200|unique:sm_class_times,period,'.$this->id,
            'start_time' => 'required|before:end_time',
            'is_break' => 'nullable',
            'end_time' => 'required',
        ];
    }
}
