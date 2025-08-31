<?php

namespace Modules\MenuManage\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SectionRequestFrom extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = auth()->user();

        return [
            'role_name' => "nullable",
            'name' => ['required', Rule::unique('permissions', 'name')->where('user_id', $user->id)->ignore($this->id)],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
