<?php

namespace App\Http\Requests\Admin\Academics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
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
            'name' => ['required', Rule::unique('sm_sections', 'section_name')->when(moduleStatusCheck('University'), function ($query): void {
                $query->where('un_academic_id', getAcademicId());
            }, function ($query): void {
                $query->where('academic_id', getAcademicId());
            })->where('school_id', auth()->user()->school_id)->ignore($this->id)],
        ];
    }
}
