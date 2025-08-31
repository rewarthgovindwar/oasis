<?php

namespace App\Http\Requests\Admin\Academics;

use Illuminate\Foundation\Http\FormRequest;

class SmAssignClassTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class' => 'required',
            'section' => 'required',
            'teacher' => 'required',
        ];
    }
}
