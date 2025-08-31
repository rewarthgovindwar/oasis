<?php

namespace App\Http\Requests\Admin\FrontSettings;

use Illuminate\Foundation\Http\FormRequest;

class SmNewsRequest extends FormRequest
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
            'title' => 'required',
            'category_id' => 'required',
            'date' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png|max:'.$maxFileSize,
            'description' => 'required',
        ];
    }
}
