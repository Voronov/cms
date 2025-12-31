<?php

namespace App\Services;

use App\Models\Form;
use Illuminate\Support\Facades\Validator;

class FormService
{
    /**
     * Generate Laravel validation rules for a form.
     */
    public function getValidationRules(Form $form): array
    {
        $rules = [];

        foreach ($form->fields as $field) {
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field['type']) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    if (!empty($field['max_size'])) {
                        $fieldRules[] = 'max:' . $field['max_size'];
                    }
                    if (!empty($field['extensions'])) {
                        $fieldRules[] = 'mimes:' . $field['extensions'];
                    }
                    break;
            }

            if (!empty($field['validation_regex'])) {
                $fieldRules[] = 'regex:' . $field['validation_regex'];
            }

            $rules[$field['name']] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Generate custom validation messages for a form.
     */
    public function getCustomMessages(Form $form): array
    {
        $messages = [];

        foreach ($form->fields as $field) {
            if (!empty($field['validation_message'])) {
                // We apply the message to all rules for this field for simplicity, 
                // or specific rules if we wanted to be more granular.
                // For now, any validation failure on this field shows the custom message.
                $messages[$field['name'] . '.*'] = $field['validation_message'];
                $messages[$field['name']] = $field['validation_message'];
            }
        }

        return $messages;
    }

    /**
     * Validate form request.
     */
    public function validateRequest(Form $form, array $data)
    {
        return Validator::make(
            $data,
            $this->getValidationRules($form),
            $this->getCustomMessages($form)
        );
    }
}
