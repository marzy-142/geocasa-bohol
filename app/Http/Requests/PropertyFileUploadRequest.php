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
            'type' => 'required|in:residential_lot,agricultural_land,commercial_lot,industrial_lot,beachfront,mountain_view,farm_land,subdivision_lot,titled_land,raw_land',
            'municipality' => 'required|string|max:100',
            'barangay' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'coordinates_lat' => 'nullable|numeric|between:-90,90',
            'coordinates_lng' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:available,sold,reserved,under_negotiation',
            
            // Property details
            'lot_area_sqm' => 'required|numeric|min:0',
            'lot_area_hectares' => 'nullable|numeric|min:0',
            'price_per_sqm' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'title_type' => 'required|string|max:100',
            'title_number' => 'required|string|max:100',
            'zoning_classification' => 'required|string|max:100',
            
            // Amenities (boolean fields)
            'road_access' => 'boolean',
            'electricity_available' => 'boolean',
            'water_source' => 'boolean',
            'internet_available' => 'boolean',
            
            // Additional fields
            'nearby_landmarks' => 'nullable|array',
            'nearby_landmarks.*' => 'string|max:255',
            'google_maps_link' => 'nullable|url|max:500',
            'additional_notes' => 'nullable|string|max:1000',
            'is_featured' => 'boolean',
            
            // Virtual tour fields
            'has_virtual_tour' => 'boolean',
            'gis_data' => 'nullable|string',
            'tour_hotspots' => 'nullable|string',
            
            // Files with enhanced validation
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            
            'virtual_tour_images' => 'nullable|array|max:20',
            'virtual_tour_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            
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