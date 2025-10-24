<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'price_per_sqm' => $this->price_per_sqm,
            'total_price' => $this->total_price,
            'address' => $this->address,
            'municipality' => $this->municipality,
            'barangay' => $this->barangay,
            'lot_area_sqm' => $this->lot_area_sqm,
            'lot_area_hectares' => $this->lot_area_hectares,
            'title_type' => $this->title_type,
            'coordinates_lat' => $this->coordinates_lat,
            'coordinates_lng' => $this->coordinates_lng,
            'road_access' => $this->road_access,
            'water_source' => $this->water_source,
            'electricity_available' => $this->electricity_available,
            'is_featured' => $this->is_featured,
            'images' => $this->images ? array_map(function($image) {
                return asset('storage/' . $image);
            }, $this->images) : [],
            'documents' => $this->documents ? array_map(function($doc) {
                return asset('storage/' . $doc);
            }, $this->documents) : [],
            'broker' => new UserResource($this->whenLoaded('broker')),
            'inquiries_count' => $this->when(isset($this->inquiries_count), $this->inquiries_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}