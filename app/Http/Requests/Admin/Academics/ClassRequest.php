<?php

namespace App\Http\Requests\Admin\Academics;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ClassRequest extends FormRequest
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
        $shift_id = shiftEnable() &&  !empty($this->shift) ? $this->shift:null;
        if (generalSetting()->result_type == 'mark') {
            return [
                'name' => ['required', 'max:200', Rule::unique('sm_classes', 'class_name')->where('academic_id', getAcademicId())
                ->when(shiftEnable() && !empty($shift_id),function($query) use($shift_id){
                    $query->where('shift_id',$shift_id);
                })->where('school_id', auth()->user()->school_id)->ignore($this->id)],
                'section' => 'required',
                'pass_mark' => 'required',
            ];
        }

        if(shiftEnable()){
            return [
                'name' => ['required', 'max:200', Rule::unique('sm_classes', 'class_name')->where('academic_id', getAcademicId())
                ->when(shiftEnable() && !empty($shift_id),function($query) use($shift_id){
                    $query->where('shift_id',$shift_id);
                })->where('school_id', auth()->user()->school_id)->ignore($this->id)],
                'section' => 'required',
                'shift' => 'required',
            ];
        }else{
            return [
                'name' => ['required', 'max:200', Rule::unique('sm_classes', 'class_name')
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)
                ->ignore($this->id)],
                'section' => 'required',
            ];
        }
    }

    

}
