<?php

namespace App\Http\Requests;

use App\Http\Requests\SecureFileUploadRequest;

class PropertyFileUploadRequest extends SecureFileUploadRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Property basic info
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'property_type' => 'required|in:house,lot,commercial,apartment,condo',
            'listing_type' => 'required|in:sale,rent',
            'status' => 'required|in:available,sold,rented,under_negotiation',
            
            // Location
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Property details
            'bedrooms' => 'nullable|integer|min:0|max:20',
            'bathrooms' => 'nullable|integer|min:0|max:20',
            'lot_area_sqm' => 'nullable|numeric|min:0',
            'floor_area_sqm' => 'nullable|numeric|min:0',
            'lot_area_hectares' => 'nullable|numeric|min:0',
            'year_built' => 'nullable|integer|min:1800|max:' . (date('Y') + 5),
            'parking_spaces' => 'nullable|integer|min:0|max:20',
            
            // Features
            'features' => 'nullable|array|max:30',
            'features.*' => 'string|max:100',
            
            // Files with enhanced validation
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB per image
            
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB per document
            
            // Virtual tour
            'virtual_tour_images' => 'nullable|array|max:20',
            'virtual_tour_images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB per tour image
            'has_virtual_tour' => 'boolean',
            'tour_hotspots' => 'nullable|json',
            
            // GIS data
            'gis_data' => 'nullable|json',
            
            // Client assignment
            'client_id' => 'nullable|exists:clients,id',
        ];
    }

    /**
     * Get security validation options for specific fields
     */
    protected function getSecurityOptionsForField(string $fieldKey): array
    {
        $options = [
            'images' => [
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
                'max_size' => 2 * 1024 * 1024 // 2MB
            ],
            'documents' => [
                'allowed_extensions' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
                'allowed_mime_types' => [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg',
                    'image/png'
                ],
                'max_size' => 5 * 1024 * 1024 // 5MB
            ],
            'virtual_tour_images' => [
                'allowed_extensions' => ['jpg', 'jpeg', 'png'],
                'allowed_mime_types' => ['image/jpeg', 'image/png'],
                'max_size' => 5 * 1024 * 1024 // 5MB
            ]
        ];

        // Handle array fields (e.g., images.0, images.1)
        $baseField = explode('.', $fieldKey)[0];
        
        return $options[$baseField] ?? [
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
            'max_size' => 5 * 1024 * 1024
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'title.required' => 'Property title is required.',
            'description.required' => 'Property description is required.',
            'price.required' => 'Property price is required.',
            'price.numeric' => 'Property price must be a valid number.',
            'price.min' => 'Property price cannot be negative.',
            
            'images.max' => 'You can upload a maximum of 10 images.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or GIF format.',
            'images.*.max' => 'Each image must not exceed 2MB.',
            
            'documents.max' => 'You can upload a maximum of 5 documents.',
            'documents.*.mimes' => 'Documents must be in PDF, DOC, DOCX, JPG, JPEG, or PNG format.',
            'documents.*.max' => 'Each document must not exceed 5MB.',
            
            'virtual_tour_images.max' => 'You can upload a maximum of 20 virtual tour images.',
            'virtual_tour_images.*.image' => 'Virtual tour files must be valid images.',
            'virtual_tour_images.*.mimes' => 'Virtual tour images must be in JPEG, PNG, or JPG format.',
            'virtual_tour_images.*.max' => 'Each virtual tour image must not exceed 5MB.',
            
            'features.max' => 'You can select a maximum of 30 features.',
            'bedrooms.max' => 'Maximum 20 bedrooms allowed.',
            'bathrooms.max' => 'Maximum 20 bathrooms allowed.',
            'parking_spaces.max' => 'Maximum 20 parking spaces allowed.',
        ]);
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'lot_area_sqm' => 'lot area (square meters)',
            'floor_area_sqm' => 'floor area (square meters)',
            'lot_area_hectares' => 'lot area (hectares)',
            'year_built' => 'year built',
            'parking_spaces' => 'parking spaces',
            'property_type' => 'property type',
            'listing_type' => 'listing type',
            'postal_code' => 'postal code',
        ]);
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Calculate hectares if not provided
        if ($this->has('lot_area_sqm') && !$this->has('lot_area_hectares')) {
            $this->merge([
                'lot_area_hectares' => $this->lot_area_sqm ? $this->lot_area_sqm / 10000 : null
            ]);
        }

        // Set default values
        $this->merge([
            'has_virtual_tour' => $this->has('virtual_tour_images') && !empty($this->virtual_tour_images),
        ]);
    }
}