<?php

namespace App\Http\Requests\Admin\GeneralSettings;

use Illuminate\Foundation\Http\FormRequest;

class SmEmailSettingsRequest extends FormRequest
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
            'from_name' => 'required_if:engine_type,smtp',
            'from_email' => 'required_if:engine_type,smtp|email',
            'mail_password' => 'required_if:engine_type,smtp',
            'mail_encryption' => 'required_if:engine_type,smtp',

        ];
    }
}
