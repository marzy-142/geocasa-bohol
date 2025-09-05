<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'property_type' => $this->property_type,
            'location' => $this->location,
            'description' => $this->description,
            'expected_price' => $this->expected_price,
            'status' => $this->status,
            'documents' => $this->documents ? array_map(function($document) {
                return asset('storage/' . $document);
            }, json_decode($this->documents, true) ?? []) : [],
            'broker' => new UserResource($this->whenLoaded('broker')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}