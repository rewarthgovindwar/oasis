<?php

namespace App\Http\Requests\Admin\StudentInfo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmStudentCategoryRequest extends FormRequest
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
        $school_id = auth()->user()->school_id;

        return [
            'category' => ['required', Rule::unique('sm_student_categories', 'category_name')->where('school_id', $school_id)->ignore($this->id)],
        ];
    }
}
