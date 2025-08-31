<?php

namespace App\Http\Requests\Admin\StudentInfo;

use Illuminate\Foundation\Http\FormRequest;

class StudentAttendanceBulkRequest extends FormRequest
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
            'attendance_date' => 'required|date',
            'file' => 'required||mimes:xls,csv,xlsx|max:'.$maxFileSize,
            'class' => 'required|integer',
            'section' => 'required|integer',
        ];
    }
}
