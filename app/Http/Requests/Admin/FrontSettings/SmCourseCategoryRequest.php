<?php

namespace App\Http\Requests\Admin\FrontSettings;

use App\SmCourseCategory;
use Illuminate\Foundation\Http\FormRequest;

class SmCourseCategoryRequest extends FormRequest
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
        $category = SmCourseCategory::find($this->id);

        return [
            'category_name' => 'required',
            'category_image' => 'required|max:'.$maxFileSize,
            'category_image' => ($category && $category->category_image)
            ? 'nullable|file|max:' . $maxFileSize
            : 'required|file|max:' . $maxFileSize,
        ];
    }
}
