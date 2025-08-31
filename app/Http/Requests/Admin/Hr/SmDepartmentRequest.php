<?php

namespace App\Http\Requests\Admin\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmDepartmentRequest extends FormRequest
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
            'name' => ['required', 'max:200', Rule::unique('sm_human_departments')->where('school_id', $school_id)->ignore($this->id)],
        ];
    }
}
