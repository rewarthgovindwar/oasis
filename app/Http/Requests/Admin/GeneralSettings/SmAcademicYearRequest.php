<?php

namespace App\Http\Requests\Admin\GeneralSettings;

use Illuminate\Foundation\Http\FormRequest;

class SmAcademicYearRequest extends FormRequest
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
            'year' => 'required|numeric|digits:4',
            'copy_with_academic_year' => 'sometimes|nullable|array',
            'starting_date' => 'required',
            'ending_date' => 'required',
            'title' => 'required|max:150',
        ];
    }
}
