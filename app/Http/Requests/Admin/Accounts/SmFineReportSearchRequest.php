<?php

namespace App\Http\Requests\Admin\Accounts;

use Illuminate\Foundation\Http\FormRequest;

class SmFineReportSearchRequest extends FormRequest
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
        if (moduleStatusCheck('University')) {
            return [
                'date_range' => 'required',
                'un_semester_label_id' => 'sometimes|nullable',
                'un_section_id' => 'sometimes|nullable',
            ];
        }

        return [
            'date_range' => 'required|date',
            'class' => 'sometimes|nullable',
            'section' => 'sometimes|nullable',
        ];

    }

    public function messages()
    {
        return [
            'date_range.required' => 'The date range field is required.',
            'date_range.date' => 'The date range field is required.',
            'un_semester_label_id.required' => 'The semester field is required.',
            'un_section_id.required' => 'The section field is required.',
            'class.required' => 'The class field is required.',
            'section.required' => 'The section field is required.',
        ];
    }
}
