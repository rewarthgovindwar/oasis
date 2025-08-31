<?php

namespace Modules\CustomField\Validators;

use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Modules\CustomField\Entities\CustomField;

class CustomFieldValidator extends Validator
{
    public function __construct(array $data, array $rules)
    {
        parent::__construct(
            app('translator'),
            $data,
            $rules
        );
    }

    protected function replaceAttributePlaceholder($message, $value)
    {
        $fieldId = (int) Str::after($value, 'field ');
        $fieldTitle = CustomField::find($fieldId)->title;
        $replacementString = sprintf('`%s` field', $fieldTitle);

        return str_replace(
            [':attribute', ':ATTRIBUTE', ':Attribute'],
            [$replacementString, Str::upper($replacementString), Str::ucfirst($replacementString)],
            $message
        );
    }
}
