<?php

namespace App\Http\Requests\Admin\Hr;

use Illuminate\Foundation\Http\FormRequest;

class staffAttendanceBulkStoreRequest extends FormRequest
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
            'attendance_date' => 'required|date',
            'file' => 'required|mimes:xlsx, csv',
        ];
    }
}
