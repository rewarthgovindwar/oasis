<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'name' => 'required',
            'start_time' => 'required|date_format:g:i A',
            'end_time' => [
            'required', 'date_format:g:i A',
                function ($attribute, $value, $fail) {
                    $startTimeStr = request()->input('start_time');
                    $endTimeStr = $value;

                    try {
                        $start = \DateTime::createFromFormat('g:i A', $startTimeStr);
                        $end = \DateTime::createFromFormat('g:i A', $endTimeStr);

                        if (!$start || !$end) {
                            return $fail('Invalid time format.');
                        }

                        // If end time is earlier than or equal to start, assume it's on the next day
                        if ($end <= $start) {
                            $end->modify('+1 day');
                        }

                        if ($end <= $start) {
                            $fail('The end time must be greater than the start time.');
                        }

                    } catch (\Exception $e) {
                        $fail('Invalid time comparison.');
                    }
                }
            ],
    ];
    }
}
