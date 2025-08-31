<?php

namespace App\Http\Requests\Admin\Transport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmAssignVehicleRequest extends FormRequest
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
            'route' => ['required', Rule::unique('sm_assign_vehicles', 'route_id')->where('school_id', $school_id)->ignore($this->id)],
            'vehicles' => 'required|array',
        ];
    }
}
