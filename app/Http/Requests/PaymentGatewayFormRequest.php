<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayFormRequest extends FormRequest
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

        $rules = [
            'gateway_name' => ['sometimes', 'nullable'],
            'service_charge' => ['sometimes', 'nullable'],
            'charge_type' => ['required_if:service_charge,1', 'in:P,F'],
        ];

        if ($this->charge_type == 'P') {
            $rules += [
                'charge' => ['sometimes', 'nullable', 'numeric', 'max:100'],
            ];
        } else {
            $rules += [
                'charge' => ['sometimes', 'nullable', 'numeric'],
            ];
        }

        if (in_array($this->gateway_name, ['Stripe', 'Paystack'])) {
            $rules += [
                'gateway_username' => 'required',
                'gateway_secret_key' => 'required',
                'gateway_publisher_key' => 'required',
            ];
        }

        if ($this->gateway_name == 'PayPal') {
            $rules += [
                'gateway_mode' => 'required|in:sandbox,live',
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'gateway_mode.in' => 'input should be sandbox or live',
            'charge_type.required_if' => 'The charge type field is required when service charge is Enable.',
        ];
    }
}
