<?php

namespace App\Http\Requests\Admin\Communicate;

use Illuminate\Foundation\Http\FormRequest;

class NoticeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'notice_title' => 'required|max:50',
            'notice_date' => 'required',
            'publish_on' => 'required',
            'notice_message' => 'sometimes|nullable',
            'is_published' => 'sometimes|nullable',
            'role' => 'sometimes|nullable|array',
        ];
    }
}
