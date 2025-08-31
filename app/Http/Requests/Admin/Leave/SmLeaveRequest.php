<?php

namespace App\Http\Requests\Admin\Leave;

use Illuminate\Foundation\Http\FormRequest;

class SmLeaveRequest extends FormRequest
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
            'apply_date' => 'required',
            'leave_define_id' => 'required',
            'leave_from' => 'required|before_or_equal:leave_to',
            'leave_to' => 'required',
            'attach_file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:'.$maxFileSize,
        ];
    }
}
