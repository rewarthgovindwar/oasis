<?php

namespace App\Http\Requests\Admin\FeesCollection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmFeesGroupRequest extends FormRequest
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
            'name' => ['required', 'max:100', Rule::unique('sm_fees_groups')->where('school_id', $school_id)->where('academic_id', getAcademicId())->ignore($this->id)],
            'description' => 'nullable|max:200',
        ];
    }
}
