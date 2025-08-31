<?php

namespace App\Http\Requests\Admin\Style;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeFormRequest extends FormRequest
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
        $rules = [
            'title' => ['required', 'max:30', Rule::unique('themes', 'title')->where('school_id', auth()->user()->school_id)->ignore($this->theme_id)],
            'background_type' => ['required', 'in:image,color'],
            'background_image' => ['required_if:background_type,image'],
            'background_color' => ['required_if:background_type,color'],
        ];
        if ($this->theme_id) {
            $rules['background_image'] = 'sometimes|nullable|image';
        }

        return $rules;
    }
}
