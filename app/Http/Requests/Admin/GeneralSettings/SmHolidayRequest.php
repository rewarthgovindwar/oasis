<?php

namespace App\Http\Requests\Admin\GeneralSettings;

use Illuminate\Foundation\Http\FormRequest;

class SmHolidayRequest extends FormRequest
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
        $maxFileSize = generalSetting()->file_size * 1024;

        return [
            'holiday_title' => 'required',
            'from_date' => 'required|before_or_equal:to_date',
            'to_date' => 'required',
            'details' => 'required',
            'upload_file_name' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt|max:'.$maxFileSize,
        ];
    }
}
