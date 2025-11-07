<?php

namespace App\Http\Requests;

use App\Http\Requests\SecureFileUploadRequest;

class SellerRequestUploadRequest extends SecureFileUploadRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Seller information with enhanced validation
            'name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s\-\.\']+$/',
            'email' => 'required|email:rfc,dns|max:255|lowercase',
            'phone' => 'required|string|max:20|min:10|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'address' => 'required|string|max:500|min:10',
            
            // Property basic information with enhanced validation
            'property_title' => 'required|string|max:255|min:5',
            'property_description' => 'required|string|max:2000|min:20',
            'property_type' => 'required|string|in:residential_lot,agricultural_land,commercial_lot,industrial_lot,beachfront,mountain_view,rice_field,coconut_plantation,subdivision_lot',
            'asking_price' => 'required|numeric|min:50000|max:999999999', // Reasonable price range
            
            // Location with enhanced validation
            'city' => 'required|string|max:100|min:2|regex:/^[a-zA-Z\s\-\.]+$/',
            'province' => 'required|string|max:100|min:2|regex:/^[a-zA-Z\s\-\.]+$/',
            'postal_code' => 'nullable|string|max:10|regex:/^[0-9\-]+$/',
            
            // Property details with validation
            'lot_area' => 'nullable|numeric|min:1|max:999999',
            
            // Property features with enhanced validation
            'features' => 'nullable|array|max:20',
            'features.*' => 'string|max:100|min:2',
            
            // Images with enhanced security validation
            'uploaded_images' => 'required|array|min:1|max:15',
            'uploaded_images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120|dimensions:min_width=400,min_height=300,max_width=4000,max_height=4000',
            
            // Property documents with enhanced validation
            'property_documents' => 'nullable|array|max:10',
            'property_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            
            // Seller preferences with validation
            'availability' => 'nullable|string|max:500',
            'urgency' => 'required|in:low,medium,high,immediate',
            
            // Additional information with validation
            'additional_notes' => 'nullable|string|max:1000',
            'marketing_consent' => 'boolean',
            // 'newsletter_consent' => 'boolean', // deprecated
            
            // Property ownership verification with enhanced validation
            'ownership_documents' => 'nullable|array|max:5',
            'ownership_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            
            // Terms with strict validation
            'terms_accepted' => 'required|accepted|boolean',
        ];
    }

    /**
     * Get security validation options for specific fields
     */
    protected function getSecurityOptionsForField(string $fieldKey): array
    {
        $options = [
            'uploaded_images' => [
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                'allowed_mime_types' => [
                    'image/jpeg',
                    'image/png',
                    'image/gif'
                ],
                'max_size' => 5 * 1024 * 1024, // 5MB
                'require_content_validation' => true,
                'scan_for_viruses' => true
            ],
            'property_documents' => [
                'allowed_extensions' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
                'allowed_mime_types' => [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg',
                    'image/png'
                ],
                'max_size' => 10 * 1024 * 1024, // 10MB
                'require_content_validation' => true,
                'scan_for_viruses' => true
            ],
            'ownership_documents' => [
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'allowed_mime_types' => [
                    'application/pdf',
                    'image/jpeg',
                    'image/png'
                ],
                'max_size' => 10 * 1024 * 1024, // 10MB
                'require_content_validation' => true,
                'scan_for_viruses' => true
            ]
        ];

        // Handle array fields (e.g., uploaded_images.0)
        $baseField = explode('.', $fieldKey)[0];
        
        return $options[$baseField] ?? [
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
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
            // Enhanced seller information messages
            'name.required' => 'Your full name is required.',
            'name.min' => 'Your name must be at least 2 characters long.',
            'name.regex' => 'Your name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.lowercase' => 'Email address must be in lowercase.',
            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number must be at least 10 characters long.',
            'phone.regex' => 'Please provide a valid phone number format.',
            'address.required' => 'Property address is required.',
            'address.min' => 'Property address must be at least 10 characters long.',
            
            // Enhanced property information messages
            'property_title.required' => 'Property title is required.',
            'property_title.min' => 'Property title must be at least 5 characters long.',
            'property_description.required' => 'Property description is required.',
            'property_description.min' => 'Property description must be at least 20 characters long.',
            'property_description.max' => 'Property description cannot exceed 2000 characters.',
            'property_type.required' => 'Property type is required.',
            'property_type.in' => 'Please select a valid property type from the list.',
            'asking_price.required' => 'Asking price is required.',
            'asking_price.numeric' => 'Asking price must be a valid number.',
            'asking_price.min' => 'Asking price must be at least ₱50,000.',
            'asking_price.max' => 'Asking price cannot exceed ₱999,999,999.',
            
            // Enhanced location messages
            'city.required' => 'City is required.',
            'city.min' => 'City name must be at least 2 characters long.',
            'city.regex' => 'City name can only contain letters, spaces, hyphens, and dots.',
            'province.required' => 'Province is required.',
            'province.min' => 'Province name must be at least 2 characters long.',
            'province.regex' => 'Province name can only contain letters, spaces, hyphens, and dots.',
            'postal_code.regex' => 'Postal code can only contain numbers and hyphens.',
            
            // Enhanced property details messages
            'lot_area.min' => 'Lot area must be at least 1 square meter.',
            'lot_area.max' => 'Lot area cannot exceed 999,999 square meters.',
            
            // Enhanced features messages
            'features.max' => 'You can select a maximum of 20 features.',
            'features.*.min' => 'Each feature must be at least 2 characters long.',
            
            // Enhanced image validation messages
            'uploaded_images.required' => 'At least one property image is required.',
            'uploaded_images.min' => 'You must upload at least 1 property image.',
            'uploaded_images.max' => 'You can upload a maximum of 15 images.',
            'uploaded_images.*.required' => 'Each uploaded file must be a valid image.',
            'uploaded_images.*.image' => 'Each file must be a valid image.',
            'uploaded_images.*.mimes' => 'Images must be in JPEG, PNG, or JPG format only.',
            'uploaded_images.*.max' => 'Each image must not exceed 5MB.',
            'uploaded_images.*.dimensions' => 'Images must be between 400x300 and 4000x4000 pixels.',
            
            // Enhanced document validation messages
            'property_documents.max' => 'You can upload a maximum of 10 property documents.',
            'property_documents.*.mimes' => 'Property documents must be in PDF, DOC, DOCX, JPG, JPEG, or PNG format.',
            'property_documents.*.max' => 'Each property document must not exceed 10MB.',
            
            'ownership_documents.max' => 'You can upload a maximum of 5 ownership documents.',
            'ownership_documents.*.mimes' => 'Ownership documents must be in PDF, JPG, JPEG, or PNG format.',
            'ownership_documents.*.max' => 'Each ownership document must not exceed 10MB.',
            
            // Enhanced preference messages
            'urgency.required' => 'Please specify the urgency level.',
            
            // Enhanced additional information messages
            'additional_notes.max' => 'Additional notes cannot exceed 1000 characters.',
            
            // Enhanced terms messages
            'terms_accepted.required' => 'You must accept the terms and conditions to proceed.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions to proceed.',
        ]);
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'property_title' => 'property title',
            'property_description' => 'property description',
            'property_type' => 'property type',
            'listing_type' => 'listing type',
            'asking_price' => 'asking price',
            'lot_area' => 'lot area',
            'year_built' => 'year built',
            'parking_spaces' => 'parking spaces',
            'additional_notes' => 'additional notes',
            'marketing_consent' => 'marketing consent',
            // 'newsletter_consent' => 'newsletter consent', // deprecated
            'terms_accepted' => 'terms and conditions',
            'property_documents' => 'property documents',
            'ownership_documents' => 'ownership documents',
        ]);
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

        // Format asking price
        if ($this->has('asking_price')) {
            $price = str_replace([',', ' '], '', $this->asking_price);
            $this->merge(['asking_price' => $price]);
        }

        // Set default consent values if not provided
        $this->merge([
            'marketing_consent' => $this->boolean('marketing_consent'),
            // 'newsletter_consent' => $this->boolean('newsletter_consent'), // deprecated
        ]);
    }

    /**
     * Configure additional validation after basic rules
     */
    public function withValidator($validator): void
    {
        parent::withValidator($validator);
        
        $validator->after(function ($validator) {
            // Validate that at least one image is uploaded
            if (!$this->hasFile('uploaded_images') || empty($this->file('uploaded_images'))) {
                $validator->errors()->add('uploaded_images', 'At least one property image is required.');
            }
            
            // Validate year built is not in the future (beyond reasonable construction time)
            if ($this->has('year_built') && $this->year_built > (date('Y') + 2)) {
                $validator->errors()->add('year_built', 'Year built cannot be more than 2 years in the future.');
            }
            
            // Enhanced business logic validation
            
            // Validate asking price is reasonable
            if ($this->has('asking_price')) {
                $askingPrice = $this->asking_price;
                $minPrice = 50000; // General minimum price for land properties
                
                if ($askingPrice < $minPrice) {
                    $validator->errors()->add('asking_price', 
                        "The asking price seems unusually low for land property. Please verify the amount."
                    );
                }
            }
            
            // Validate lot area is reasonable
            if ($this->has('lot_area') && $this->lot_area) {
                $lotArea = $this->lot_area;
                $minArea = 100; // General minimum area for land properties
                
                if ($lotArea < $minArea) {
                    $validator->errors()->add('lot_area', 
                        "The lot area seems unusually small for land property. Please verify the measurement."
                    );
                }
            }
            
            // Validate phone number format more strictly
            if ($this->has('phone')) {
                $phone = preg_replace('/[^0-9]/', '', $this->phone);
                if (strlen($phone) < 10 || strlen($phone) > 15) {
                    $validator->errors()->add('phone', 'Phone number must be between 10 and 15 digits.');
                }
            }
            
            // Validate email domain is not from temporary email services
            if ($this->has('email')) {
                $tempEmailDomains = [
                    '10minutemail.com', 'guerrillamail.com', 'mailinator.com', 
                    'tempmail.org', 'throwaway.email', 'temp-mail.org'
                ];
                
                $emailDomain = substr(strrchr($this->email, "@"), 1);
                if (in_array(strtolower($emailDomain), $tempEmailDomains)) {
                    $validator->errors()->add('email', 
                        'Please use a permanent email address. Temporary email services are not allowed.'
                    );
                }
            }
            
            // Validate property description quality
            if ($this->has('property_description')) {
                $description = strtolower($this->property_description);
                $spamWords = ['buy now', 'click here', 'limited time', 'act fast', 'guaranteed'];
                
                foreach ($spamWords as $spamWord) {
                    if (strpos($description, $spamWord) !== false) {
                        $validator->errors()->add('property_description', 
                            'Property description contains promotional language that is not allowed.'
                        );
                        break;
                    }
                }
            }
            
            // Validate file uploads more thoroughly
            if ($this->hasFile('uploaded_images')) {
                $images = $this->file('uploaded_images');
                $totalSize = 0;
                
                foreach ($images as $image) {
                    $totalSize += $image->getSize();
                }
                
                // Check total upload size (max 50MB for all images combined)
                if ($totalSize > 50 * 1024 * 1024) {
                    $validator->errors()->add('uploaded_images', 
                        'Total size of all images cannot exceed 50MB.'
                    );
                }
            }
        });
    }
}