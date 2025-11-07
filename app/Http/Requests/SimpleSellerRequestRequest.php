<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimpleSellerRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Contact Information (form sends contact_name, contact_email, contact_phone)
            'contact_name' => 'required|string|max:255|min:2',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20|min:10',
            
            // Property Basic Info
            'property_title' => 'required|string|max:255|min:5',
            'property_description' => 'required|string|max:2000|min:20',
            'property_type' => 'required|array|min:1',
            'property_type.*' => 'required|string',
            'custom_property_type' => 'nullable|string|max:100|required_if:property_type.*,other',
            
            // Pricing & Area (form sends lot_area_sqm not lot_area)
            'asking_price' => 'required|numeric|min:50000|max:999999999',
            'lot_area_sqm' => 'required|numeric|min:1|max:999999',
            'price_expectation' => 'nullable|string|max:500',
            
            // Location Details
            'municipality' => 'required|string|max:100',
            'barangay' => 'required|string|max:100',
            'address' => 'nullable|string|max:500',
            'nearby_landmarks' => 'nullable|string|max:500',
            
            // Title Information
            'title_type' => 'required|string|max:100',
            'title_number' => 'nullable|string|max:100',
            'zoning_classification' => 'nullable|string|max:100',
            
            // Property Features
            'features' => 'nullable|array',
            'features.*' => 'string',
            
            // Utilities & Access
            'road_access' => 'nullable|boolean',
            'water_source' => 'nullable|boolean',
            'electricity' => 'nullable|boolean',
            'internet' => 'nullable|boolean',
            
            // GIS/Location Data
            'coordinates_lat' => 'nullable|numeric|between:-90,90',
            'coordinates_lng' => 'nullable|numeric|between:-180,180',
            
            // File Uploads (accept both property_images and uploaded_images for compatibility)
            'property_images' => 'nullable|array|min:1|max:15',
            'property_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            
            'uploaded_images' => 'nullable|array|min:1|max:15',
            'uploaded_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            
            'property_documents' => 'nullable|array|max:10',
            'property_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            
            'ownership_documents' => 'nullable|array|max:10',
            'ownership_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            
            // Additional Information
            'additional_notes' => 'nullable|string|max:2000',
            'urgency_level' => 'nullable|string|in:low,medium,high',
            'preferred_contact_method' => 'nullable|string|in:phone,email,both',
            'best_time_to_contact' => 'nullable|string|max:100',
            
            // Consent & Terms
            'marketing_consent' => 'nullable|boolean',
            // 'newsletter_consent' => 'nullable|boolean', // deprecated
            'terms_accepted' => 'required|accepted',
            
            // Broker Selection
            'broker_selection_method' => 'required|in:manual',
            'preferred_broker_id' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            // Contact
            'contact_name.required' => 'Your full name is required.',
            'contact_email.required' => 'Email address is required.',
            'contact_email.email' => 'Please provide a valid email address.',
            'contact_phone.required' => 'Phone number is required.',
            'contact_phone.min' => 'Phone number must be at least 10 digits.',
            
            // Property Info
            'property_title.required' => 'Property title is required.',
            'property_title.min' => 'Property title must be at least 5 characters.',
            'property_description.required' => 'Property description is required.',
            'property_description.min' => 'Property description must be at least 20 characters.',
            'property_type.required' => 'Please select at least one property type.',
            'property_type.min' => 'Please select at least one property type.',
            'custom_property_type.required_if' => 'Please specify the custom property type.',
            
            // Pricing & Area
            'asking_price.required' => 'Asking price is required.',
            'asking_price.min' => 'Asking price must be at least ₱50,000.',
            'lot_area_sqm.required' => 'Lot area is required.',
            'lot_area_sqm.min' => 'Lot area must be at least 1 square meter.',
            
            // Location
            'municipality.required' => 'Municipality is required.',
            'barangay.required' => 'Barangay is required.',
            
            // Title
            'title_type.required' => 'Title type is required.',
            'title_type.in' => 'Please select a valid title type.',
            
            // Images
            'property_images.required' => 'At least one property image is required.',
            'property_images.min' => 'Please upload at least one property image.',
            'property_images.max' => 'You can upload a maximum of 15 images.',
            'property_images.*.image' => 'Each file must be a valid image.',
            'property_images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or GIF format.',
            'property_images.*.max' => 'Each image must not exceed 5MB.',
            
            'uploaded_images.required' => 'At least one property image is required.',
            'uploaded_images.min' => 'Please upload at least one property image.',
            'uploaded_images.max' => 'You can upload a maximum of 15 images.',
            'uploaded_images.*.image' => 'Each file must be a valid image.',
            'uploaded_images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or GIF format.',
            'uploaded_images.*.max' => 'Each image must not exceed 5MB.',
            
            // Documents
            'property_documents.*.mimes' => 'Property documents must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
            'property_documents.*.max' => 'Each document must not exceed 10MB.',
            'ownership_documents.*.mimes' => 'Ownership documents must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
            'ownership_documents.*.max' => 'Each document must not exceed 10MB.',
            
            // Terms
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
            
            // Broker
            'broker_selection_method.required' => 'Broker selection method is required.',
            'preferred_broker_id.required' => 'Please select a broker.',
            'preferred_broker_id.exists' => 'The selected broker is invalid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean phone number
        if ($this->has('contact_phone')) {
            $phone = preg_replace('/[^0-9+]/', '', $this->contact_phone);
            $this->merge(['contact_phone' => $phone]);
        }

        // Format asking price (remove commas and spaces)
        if ($this->has('asking_price')) {
            $price = str_replace([',', ' ', '₱'], '', $this->asking_price);
            $this->merge(['asking_price' => $price]);
        }

        // Format lot area
        if ($this->has('lot_area_sqm')) {
            $area = str_replace([',', ' '], '', $this->lot_area_sqm);
            $this->merge(['lot_area_sqm' => $area]);
        }

        // Ensure boolean values are properly set
        $this->merge([
            'marketing_consent' => $this->boolean('marketing_consent'),
            // 'newsletter_consent' => $this->boolean('newsletter_consent'), // deprecated
            'road_access' => $this->boolean('road_access'),
            'water_source' => $this->boolean('water_source'),
            'electricity' => $this->boolean('electricity'),
            'internet' => $this->boolean('internet'),
        ]);

        // Default broker selection method to manual
        if (!$this->has('broker_selection_method')) {
            $this->merge(['broker_selection_method' => 'manual']);
        }
    }
    
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Ensure at least one image field has files
            $hasPropertyImages = $this->hasFile('property_images') && !empty($this->file('property_images'));
            $hasUploadedImages = $this->hasFile('uploaded_images') && !empty($this->file('uploaded_images'));
            
            if (!$hasPropertyImages && !$hasUploadedImages) {
                $validator->errors()->add('uploaded_images', 'At least one property image is required.');
            }
        });
    }
}