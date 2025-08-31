<?php

namespace App\Http\Requests\Admin\FeesCollection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmFeesTypeRequest extends FormRequest
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
            'name' => ['required', 'max:50', Rule::unique('sm_fees_types')->where('school_id', $school_id)->where('fees_group_id', $this->fees_group)->ignore($this->id)],
            'fees_group' => 'required|integer',
            'description' => 'nullable|max:200',
        ];
    }
}
