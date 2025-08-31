<?php

namespace App\Http\Requests\Admin\FeesCollection;

use Illuminate\Foundation\Http\FormRequest;

class SmFeesBankPaymentRequest extends FormRequest
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
        return [
            'class' => 'sometimes|nullable',
            'section' => 'sometimes|nullable',
            'payment_date' => 'sometimes|nullable',
            'approve_status' => 'sometimes|nullable',
        ];
    }
}
