<?php

namespace App\Http\Requests\Admin\AdminSection;

use Illuminate\Foundation\Http\FormRequest;

class SmPostalDispatchRequest extends FormRequest
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
            'from_title' => 'required|max:250',
            'reference_no' => 'required|max:150',
            'address' => 'required|max:250',
            'to_title' => 'required|max:250',
            'note' => 'required',
            'date' => 'required|date',
            'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt|max:'.$maxFileSize,
        ];
    }
}
