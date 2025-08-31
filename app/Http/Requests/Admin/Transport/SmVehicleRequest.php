<?php

namespace App\Http\Requests\Admin\Transport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmVehicleRequest extends FormRequest
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
            'vehicle_number' => ['required', 'max:200', Rule::unique('sm_vehicles', 'vehicle_no')->where('school_id', $school_id)->ignore($this->id)],
            'vehicle_model' => 'required|max:200',
            'year_made' => 'sometimes|nullable|max:10',
            'note' => 'sometimes|nullable',
            'driver_id' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'driver_id' => 'driver',
        ];
    }
}
