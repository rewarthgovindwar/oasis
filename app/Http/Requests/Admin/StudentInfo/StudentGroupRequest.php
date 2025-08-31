<?php

namespace App\Http\Requests\Admin\StudentInfo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentGroupRequest extends FormRequest
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
            'group' => ['required', Rule::unique('sm_student_groups')->where('school_id', auth()->user()->school_id)->ignore($this->id)],
        ];
    }
}
