<?php

namespace App\Http\Requests\Admin\FrontSettings;

use Illuminate\Foundation\Http\FormRequest;

class SmTestimonialRequest extends FormRequest
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
        $rules = [
            'name' => 'required|max:100',
            'designation' => 'required|max:100',
            'institution_name' => 'required|max:100',
            'description' => 'required|max:250',
            'rating' => 'required',
        ];
        if ($this->id) {
            $rules['image'] = 'sometimes|nullable|mimes:jpg,jpeg,png|max:'.$maxFileSize;
        } else {
            $rules['image'] = 'required|mimes:jpg,jpeg,png|max:'.$maxFileSize;
        }

        return $rules;
    }
}
