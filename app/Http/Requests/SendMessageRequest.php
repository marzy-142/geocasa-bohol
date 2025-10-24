<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => [
                'nullable', // Changed from 'required' to 'nullable' for conversation creation
                'string',
                'min:1',
                'max:5000', // Max 5000 characters
                function ($attribute, $value, $fail) {
                    // Only check spam if content is provided
                    if ($value && $this->containsSpam($value)) {
                        $fail('The message contains inappropriate content or spam.');
                    }
                },
            ],
            'attachments' => 'nullable|array|max:5', // Max 5 attachments
            'attachments.*' => 'file|max:10240', // Max 10MB per file
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Message content cannot be empty.',
            'content.max' => 'Message is too long. Maximum 5000 characters allowed.',
            'attachments.max' => 'You can only attach up to 5 files.',
            'attachments.*.max' => 'Each file must be less than 10MB.',
        ];
    }

    /**
     * Basic spam detection
     */
    protected function containsSpam(string $content): bool
    {
        // Convert to lowercase for checking
        $lowerContent = strtolower($content);
        
        // Spam patterns
        $spamPatterns = [
            '/\b(viagra|cialis|casino|lottery|winner)\b/i',
            '/\b(click here|buy now|limited time)\b/i',
            '/http[s]?:\/\/[^\s]{50,}/', // Very long URLs
            '/(.)\1{10,}/', // Repeated characters (10+ times)
        ];
        
        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        // Check for excessive links (more than 3)
        if (substr_count($lowerContent, 'http') > 3) {
            return true;
        }
        
        // Check for excessive caps (more than 70% uppercase)
        $uppercaseCount = strlen(preg_replace('/[^A-Z]/', '', $content));
        $totalLetters = strlen(preg_replace('/[^A-Za-z]/', '', $content));
        
        if ($totalLetters > 10 && ($uppercaseCount / $totalLetters) > 0.7) {
            return true;
        }
        
        return false;
    }
}
