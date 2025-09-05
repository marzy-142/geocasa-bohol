<?php

namespace App\Http\Requests;

use App\Http\Requests\SecureFileUploadRequest;
use Illuminate\Validation\Rules\Password;

class BrokerRegistrationRequest extends SecureFileUploadRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic user information
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:client,broker',
            
            // Professional Details
            'prc_id' => 'required|string|max:50|unique:users,prc_id',
            'prc_license_expiration' => 'required|date|after:today',
            'prc_id_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'years_experience' => 'nullable|integer|min:0|max:50',
            
            // Business Details (Optional)
            'brokerage_firm_name' => 'nullable|string|max:255',
            'office_address' => 'nullable|string|max:500',
            'office_contact_number' => 'nullable|string|max:20',
            
            // Remove these unused validation rules:
            // 'company_address' => 'nullable|string|max:500',
            // 'specialization' => 'nullable|array|max:10',
            // 'specialization.*' => 'string|in:residential,commercial,industrial,agricultural,luxury,rental',
            // 'bio' => 'nullable|string|max:1000',
            // 'website' => 'nullable|url|max:255', 
            // 'social_media' => 'nullable|json',
            
            // Location
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100', 
            'address' => 'required|string|max:500',
            'postal_code' => 'nullable|string|max:10',
            
            // Documents
            'business_permit_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'additional_documents' => 'nullable|array|max:5',
            'additional_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            
            // Agreements
            'terms_accepted' => 'required|accepted',
            'privacy_policy_accepted' => 'required|accepted', 
            'information_certified' => 'required|accepted',
            'prc_verification_consent' => 'required|accepted',
        ];
    }

    /**
     * Get security validation options for specific fields
     */
    protected function getSecurityOptionsForField(string $fieldKey): array
    {
        $options = [
            'prc_id_file' => [
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'allowed_mime_types' => [
                    'application/pdf',
                    'image/jpeg',
                    'image/png'
                ],
                'max_size' => 5 * 1024 * 1024, // 5MB
                'require_content_validation' => true, // Validate file content matches extension
                'scan_for_viruses' => true
            ],
            'business_permit_file' => [
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'allowed_mime_types' => [
                    'application/pdf',
                    'image/jpeg',
                    'image/png'
                ],
                'max_size' => 5 * 1024 * 1024, // 5MB
                'require_content_validation' => true,
                'scan_for_viruses' => true
            ],
            'additional_documents' => [
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
                'allowed_mime_types' => [
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ],
                'max_size' => 5 * 1024 * 1024, // 5MB
                'require_content_validation' => true,
                'scan_for_viruses' => true
            ]
        ];

        // Handle array fields (e.g., additional_documents.0)
        $baseField = explode('.', $fieldKey)[0];
        
        return $options[$baseField] ?? [
            'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_size' => 5 * 1024 * 1024,
            'require_content_validation' => true,
            'scan_for_viruses' => true
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required' => 'Full name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.required' => 'Phone number is required.',
            
            'license_number.required' => 'PRC license number is required.',
            'license_number.unique' => 'This license number is already registered.',
            
            // Enhanced PRC ID file error messages
            'prc_id_file.file' => 'Please select a valid file for your PRC ID document.',
            'prc_id_file.mimes' => 'PRC ID document must be in JPG, JPEG, PNG, or PDF format. Please convert your file to one of these formats.',
            'prc_id_file.max' => 'PRC ID document file size must not exceed 10MB. Please compress your file or use a smaller image.',
            'prc_id_file.uploaded' => 'PRC ID document upload failed. Please try again or use a different file.',
            
            // Enhanced business permit file error messages
            'business_permit_file.file' => 'Please select a valid file for your business permit document.',
            'business_permit_file.mimes' => 'Business permit document must be in JPG, JPEG, PNG, or PDF format. Please convert your file to one of these formats.',
            'business_permit_file.max' => 'Business permit document file size must not exceed 5MB. Please compress your file or use a smaller image.',
            'business_permit_file.uploaded' => 'Business permit document upload failed. Please try again or use a different file.',
            
            // Enhanced additional documents error messages
            'additional_documents.max' => 'You can upload a maximum of 5 additional documents.',
            'additional_documents.*.file' => 'One or more additional documents are invalid. Please select valid files.',
            'additional_documents.*.mimes' => 'Additional documents must be in PDF, JPG, JPEG, PNG, DOC, or DOCX format.',
            'additional_documents.*.max' => 'Each additional document must not exceed 5MB. Please compress large files.',
            'additional_documents.*.uploaded' => 'One or more additional documents failed to upload. Please try again.',
            
            'years_experience.max' => 'Years of experience cannot exceed 50.',
            'specialization.max' => 'You can select a maximum of 10 specializations.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',
            
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
            'privacy_policy_accepted.required' => 'You must accept the privacy policy.',
            'privacy_policy_accepted.accepted' => 'You must accept the privacy policy.',
            'information_certified.required' => 'You must certify that the information provided is accurate.',
            'information_certified.accepted' => 'You must certify that the information provided is accurate.',
            'prc_verification_consent.required' => 'You must consent to PRC license verification.',
            'prc_verification_consent.accepted' => 'You must consent to PRC license verification.',
            
            // Location fields
            'city.required' => 'City is required for broker registration.',
            'province.required' => 'Province is required for broker registration.',
            'address.required' => 'Complete address is required for broker registration.',
            
            // PRC fields
            'prc_id.required' => 'PRC license number is required for broker registration.',
            'prc_id.unique' => 'This PRC license number is already registered in our system.',
            'prc_license_expiration.required' => 'PRC license expiration date is required.',
            'prc_license_expiration.after' => 'PRC license must be valid (not expired). Please renew your license first.',
        ]);
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'role' => 'role',
            'prc_id' => 'PRC ID',
            'prc_id_file' => 'PRC ID file',
            'company_address' => 'company address',
            'years_experience' => 'years of experience',
            'postal_code' => 'postal code',
            'terms_accepted' => 'terms and conditions',
            'privacy_policy_accepted' => 'privacy policy',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Clean and format phone number
        if ($this->has('phone')) {
            $phone = preg_replace('/[^0-9+]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }

        // Clean license number
        if ($this->has('license_number')) {
            $licenseNumber = strtoupper(trim($this->license_number));
            $this->merge(['license_number' => $licenseNumber]);
        }

        // Parse social media JSON if provided as string
        if ($this->has('social_media') && is_string($this->social_media)) {
            try {
                $socialMedia = json_decode($this->social_media, true);
                $this->merge(['social_media' => $socialMedia]);
            } catch (\Exception $e) {
                // Leave as is, validation will catch invalid JSON
            }
        }
    }

    /**
     * Configure additional validation after basic rules
     */
    public function withValidator($validator): void
    {
        parent::withValidator($validator);
        
        $validator->after(function ($validator) {
            // Validate social media URLs if provided
            if ($this->has('social_media') && is_array($this->social_media)) {
                foreach ($this->social_media as $platform => $url) {
                    if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
                        $validator->errors()->add('social_media', "Invalid URL for {$platform}.");
                    }
                }
            }
            
            // Validate license number format (example: PRC-123456)
            if ($this->has('license_number')) {
                $licenseNumber = $this->license_number;
                if (!preg_match('/^[A-Z0-9-]{5,20}$/', $licenseNumber)) {
                    $validator->errors()->add('license_number', 'License number format is invalid.');
                }
            }
        });
    }
}