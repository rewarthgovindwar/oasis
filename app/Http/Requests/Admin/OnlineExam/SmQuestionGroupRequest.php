<?php

namespace App\Http\Requests\Admin\OnlineExam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmQuestionGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', Rule::unique('sm_question_groups', 'title')->ignore($this->id)],

        ];
    }
}
