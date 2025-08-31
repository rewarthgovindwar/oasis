<?php

namespace App\Http\Requests\Admin\Dormitory;

use Illuminate\Foundation\Http\FormRequest;

class SmDormitoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dormitory_name' => 'required|max:200',
            'type' => 'required',
            'address' => 'required',
            'intake' => 'required',
            'description' => 'sometimes|nullable|max:200',
        ];
    }
}
