<?php

namespace App\Http\Requests\Admin\FrontSettings;

use Illuminate\Foundation\Http\FormRequest;

class SocialMediaRequest extends FormRequest
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
            'url' => 'required',
            'icon' => 'required',
            'status' => 'required',

        ];
    }
}
