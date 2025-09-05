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
            // Seller information
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            
            // Property basic information
            'property_title' => 'required|string|max:255',
            'property_description' => 'required|string|max:2000',
            'property_type' => 'required|in:house,lot,commercial,apartment,condo,townhouse,farm',
            'listing_type' => 'required|in:sale,rent',
            'asking_price' => 'required|numeric|min:0',
            
            // Location
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            
            // Property details
            'bedrooms' => 'nullable|integer|min:0|max:20',
            'bathrooms' => 'nullable|integer|min:0|max:20',
            'lot_area' => 'nullable|numeric|min:0',
            'floor_area' => 'nullable|numeric|min:0',
            'year_built' => 'nullable|integer|min:1800|max:' . (date('Y') + 5),
            'parking_spaces' => 'nullable|integer|min:0|max:20',
            
            // Property features
            'features' => 'nullable|array|max:20',
            'features.*' => 'string|max:100',
            
            // Images with enhanced validation
            'uploaded_images' => 'nullable|array|max:15',
            'uploaded_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB per image
            
            // Property documents
            'property_documents' => 'nullable|array|max:10',
            'property_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB per document
            
            // Seller preferences
            'preferred_contact_method' => 'required|in:email,phone,both',
            'availability' => 'nullable|string|max:500',
            'urgency' => 'required|in:low,medium,high,urgent',
            
            // Additional information
            'additional_notes' => 'nullable|string|max:1000',
            'marketing_consent' => 'boolean',
            'newsletter_consent' => 'boolean',
            
            // Property ownership verification
            'ownership_documents' => 'nullable|array|max:5',
            'ownership_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB per document
            
            // Terms
            'terms_accepted' => 'required|accepted',
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
            'name.required' => 'Your full name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'phone.required' => 'Phone number is required.',
            
            'property_title.required' => 'Property title is required.',
            'property_description.required' => 'Property description is required.',
            'property_description.max' => 'Property description cannot exceed 2000 characters.',
            'property_type.required' => 'Property type is required.',
            'listing_type.required' => 'Listing type (sale/rent) is required.',
            'asking_price.required' => 'Asking price is required.',
            'asking_price.numeric' => 'Asking price must be a valid number.',
            'asking_price.min' => 'Asking price cannot be negative.',
            
            'address.required' => 'Property address is required.',
            'city.required' => 'City is required.',
            'province.required' => 'Province is required.',
            
            'uploaded_images.max' => 'You can upload a maximum of 15 images.',
            'uploaded_images.*.image' => 'Each file must be a valid image.',
            'uploaded_images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or GIF format.',
            'uploaded_images.*.max' => 'Each image must not exceed 5MB.',
            
            'property_documents.max' => 'You can upload a maximum of 10 property documents.',
            'property_documents.*.mimes' => 'Property documents must be in PDF, DOC, DOCX, JPG, JPEG, or PNG format.',
            'property_documents.*.max' => 'Each property document must not exceed 10MB.',
            
            'ownership_documents.max' => 'You can upload a maximum of 5 ownership documents.',
            'ownership_documents.*.mimes' => 'Ownership documents must be in PDF, JPG, JPEG, or PNG format.',
            'ownership_documents.*.max' => 'Each ownership document must not exceed 10MB.',
            
            'preferred_contact_method.required' => 'Please specify your preferred contact method.',
            'urgency.required' => 'Please specify the urgency level.',
            
            'bedrooms.max' => 'Maximum 20 bedrooms allowed.',
            'bathrooms.max' => 'Maximum 20 bathrooms allowed.',
            'parking_spaces.max' => 'Maximum 20 parking spaces allowed.',
            'features.max' => 'You can select a maximum of 20 features.',
            'additional_notes.max' => 'Additional notes cannot exceed 1000 characters.',
            
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
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
            'floor_area' => 'floor area',
            'year_built' => 'year built',
            'parking_spaces' => 'parking spaces',
            'preferred_contact_method' => 'preferred contact method',
            'additional_notes' => 'additional notes',
            'marketing_consent' => 'marketing consent',
            'newsletter_consent' => 'newsletter consent',
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
            'newsletter_consent' => $this->boolean('newsletter_consent'),
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
            
            // Validate lot area and floor area relationship for houses
            if ($this->property_type === 'house' && $this->has(['lot_area', 'floor_area'])) {
                if ($this->floor_area > $this->lot_area) {
                    $validator->errors()->add('floor_area', 'Floor area cannot be larger than lot area.');
                }
            }
        });
    }
}