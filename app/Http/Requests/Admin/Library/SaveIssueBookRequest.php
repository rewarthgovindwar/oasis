<?php

namespace App\Http\Requests\Admin\Library;

use Illuminate\Foundation\Http\FormRequest;

class SaveIssueBookRequest extends FormRequest
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
            'book_id' => 'required',
            'due_date' => 'required|after_or_equal:'.date('m/d/Y'),
        ];
    }
}
