<?php

namespace Modules\BehaviourRecords\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncidentCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'comment' => 'required',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
