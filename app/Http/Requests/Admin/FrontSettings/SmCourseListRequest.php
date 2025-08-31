<?php

namespace App\Http\Requests\Admin\FrontSettings;

use App\SmCourse;
use Illuminate\Foundation\Http\FormRequest;

class SmCourseListRequest extends FormRequest
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
        $course = SmCourse::find($this->id);
        $rules = [
            'title' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png|max:'.$maxFileSize,
            'category_id' => 'required',
            'overview' => 'nullable',
            'outline' => 'nullable',
            'prerequisites' => 'nullable',
            'resources' => 'nullable',
            'stats' => 'nullable',
        ];

        if ($course && $course->image) {
            $rules['image'] = 'mimes:jpg,jpeg,png|max:'.$maxFileSize;
        }

        return $rules;
    }
}
