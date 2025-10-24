<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StandardPassword implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Length check
        if (strlen($value) < 12) {
            return false;
        }
        
        // Lowercase check
        if (!preg_match('/[a-z]/', $value)) {
            return false;
        }
        
        // Uppercase check
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }
        
        // Number check
        if (!preg_match('/\d/', $value)) {
            return false;
        }
        
        // Special character check - includes underscore and other common special chars
        if (!preg_match('/[@$!%*?&_\-+=\[\]{}|\\:";\'<>.,\/~`]/', $value)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be at least 12 characters long and contain uppercase letters, lowercase letters, numbers, and special characters (@$!%*?&_ etc.).';
    }

    /**
     * Get the standard password rules array for use in validation
     *
     * @return array
     */
    public static function rules(): array
    {
        return [
            'required',
            'confirmed',
            'min:12',
            'max:128',
            function ($attribute, $value, $fail) {
                // Custom password validation that includes underscore and other common special chars
                $errors = [];
                
                // Length check
                if (strlen($value) < 12) {
                    $errors[] = 'at least 12 characters';
                }
                
                // Lowercase check
                if (!preg_match('/[a-z]/', $value)) {
                    $errors[] = 'lowercase letters';
                }
                
                // Uppercase check
                if (!preg_match('/[A-Z]/', $value)) {
                    $errors[] = 'uppercase letters';
                }
                
                // Number check
                if (!preg_match('/\d/', $value)) {
                    $errors[] = 'numbers';
                }
                
                // Special character check - includes underscore and other common special chars
                if (!preg_match('/[@$!%*?&_\-+=\[\]{}|\\:";\'<>.,\/~`]/', $value)) {
                    $errors[] = 'special characters (@$!%*?&_ etc.)';
                }
                
                if (!empty($errors)) {
                    $fail("The {$attribute} must contain " . implode(', ', $errors) . '.');
                }
            }
        ];
    }
}
