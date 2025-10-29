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
            // Basic seller info
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20|min:10',
            'address' => 'required|string|max:500|min:10',
            
            // Property info
            'property_title' => 'required|string|max:255|min:5',
            'property_description' => 'required|string|max:2000|min:20',
            'property_type' => 'required|string|in:residential_lot,agricultural_land,commercial_lot,industrial_lot,beachfront,mountain_view,rice_field,coconut_plantation,subdivision_lot,titled_land,tax_declared',
            'asking_price' => 'required|numeric|min:50000|max:999999999',
            
            // Location
            'city' => 'required|string|max:100|min:2',
            'province' => 'required|string|max:100|min:2',
            'postal_code' => 'nullable|string|max:10',
            'lot_area' => 'nullable|numeric|min:1|max:999999',
            
            // Features
            'features' => 'nullable|array|max:20',
            'features.*' => 'string|max:100|min:2',
            
            // Files - SIMPLIFIED
            'uploaded_images' => 'required|array|min:1|max:15',
            'uploaded_images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            
            'property_documents' => 'nullable|array|max:10',
            'property_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            
            'ownership_documents' => 'nullable|array|max:5',
            'ownership_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            
            // Preferences
            'availability' => 'nullable|string|max:500',
            'urgency' => 'required|in:low,medium,high,immediate',
            'additional_notes' => 'nullable|string|max:1000',
            
            // Consent
            'marketing_consent' => 'boolean',
            'newsletter_consent' => 'boolean',
            'terms_accepted' => 'required|accepted',
            
            // Broker selection
            'broker_selection_method' => 'required|in:auto,manual',
            'preferred_broker_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Your full name is required.',
            'email.required' => 'Email address is required.',
            'phone.required' => 'Phone number is required.',
            'address.required' => 'Property address is required.',
            'property_title.required' => 'Property title is required.',
            'property_description.required' => 'Property description is required.',
            'asking_price.required' => 'Asking price is required.',
            'city.required' => 'City is required.',
            'province.required' => 'Province is required.',
            'uploaded_images.required' => 'At least one property image is required.',
            'uploaded_images.*.image' => 'Each file must be a valid image.',
            'uploaded_images.*.mimes' => 'Images must be in JPEG, PNG, or JPG format.',
            'uploaded_images.*.max' => 'Each image must not exceed 5MB.',
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean phone number
        if ($this->has('phone')) {
            $phone = preg_replace('/[^0-9+]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }

        // Format asking price
        if ($this->has('asking_price')) {
            $price = str_replace([',', ' '], '', $this->asking_price);
            $this->merge(['asking_price' => $price]);
        }

        // Set default consent values
        $this->merge([
            'marketing_consent' => $this->boolean('marketing_consent'),
            'newsletter_consent' => $this->boolean('newsletter_consent'),
        ]);
    }
}

