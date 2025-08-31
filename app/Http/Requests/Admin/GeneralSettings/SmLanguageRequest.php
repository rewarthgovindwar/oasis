<?php

namespace App\Http\Requests\Admin\GeneralSettings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmLanguageRequest extends FormRequest
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
        $school_id = auth()->user()->school_id;

        return [
            'name' => ['required', Rule::unique('languages')->where('school_id', $school_id)->ignore($this->id)],
            'code' => 'required | max:15',
            'native' => 'required | max:50',
            'rtl' => 'required',
        ];
    }
}
