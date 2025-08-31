<?php

namespace App\Http\Requests\Admin\StudentInfo;

use Illuminate\Foundation\Http\FormRequest;

class StudentSubjectWiseAttendanceSearchRequest extends FormRequest
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
        if (moduleStatusCheck('University')) {
            return [
                'month' => 'required',
                'year' => 'required',
                'un_session_id' => 'required',
                'un_faculty_id' => 'required',
                'un_department_id' => 'sometimes|nullable',
                'un_academic_id' => 'required',
                'un_semester_id' => 'required',
                'un_semester_label_id' => 'required',

            ];
        }
        return [
            'class' => 'required',
            'section' => 'required',
            'month' => 'required',
            'year' => 'required',
        ];
    }
}
