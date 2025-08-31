<?php

namespace App\Http\Requests\Admin\FeesCollection;

use Illuminate\Foundation\Http\FormRequest;

class SmFeesMasterRequest extends FormRequest
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
                'name' => 'required',
                'amount' => 'required',
            ];
        }

        if (directFees()) {
            return [
                'name' => 'required',
                'amount' => 'required',
                'class' => 'required',
                'section_id' => 'required',
                'unPercentage' => 'required',
                'totalInstallmentAmount' => 'required|same:amount',
            ];

        }

        return [
            'fees_type' => 'required',
            'date' => 'required|date',
            'amount' => 'required',
        ];

    }
}
