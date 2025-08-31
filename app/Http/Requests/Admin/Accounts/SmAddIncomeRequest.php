<?php

namespace App\Http\Requests\Admin\Accounts;

use Illuminate\Foundation\Http\FormRequest;

class SmAddIncomeRequest extends FormRequest
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
            'income_head' => 'required',
            'name' => 'required',
            'date' => 'required|date',
            'accounts' => 'required_if:payment_method,Bank',
            'payment_method' => 'required',
            'amount' => 'required',
            'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:'.$maxFileSize,
            'description' => 'sometimes|nullable|max:200',
        ];
    }
}
