<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Models\SmCustomField;

trait CustomFields
{
    public function storeFields($model, $fields, $form_name) {}

    public function generateValidateRules($form_name, $model = null): array
    {

        $fields = SmCustomField::where(['form_name' => $form_name])->when(auth()->check(), function ($q): void {
            $q->where('school_id', auth()->user()->school_id);
        })->get();
        $rules = [];
        $custom_fields = ($model && $model->custom_field) ? json_decode($model->custom_field, true) : [];

        if (count($fields) > 0) {
            foreach ($fields as $field) {
                $field_rule = [];
                $field_name  = str_replace('-','_',Str::slug($field->label));
                $field->required ? (is_show('custom_field') ? array_push($field_rule, 'required') : null) : array_push($field_rule, 'nullable');
                if ($field->type === 'fileInput') {
                    $rules['customF.'.$field_name] = gv($custom_fields, $field_name) ? [] : $field_rule;
                } else {

                    $rules['customF.'.$field_name] = $field_rule;
                }

            }
        }

        return $rules;
    }
}
