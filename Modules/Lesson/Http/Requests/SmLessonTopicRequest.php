<?php

namespace Modules\Lesson\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SmLessonTopicRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'class' => 'required',
            'subject' => 'required',
            'section' => 'required',
            'lesson' => 'required',
            'topic' => 'required|array',
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
