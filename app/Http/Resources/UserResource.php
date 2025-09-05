<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'role' => $this->role,
            'phone' => $this->phone,
            'address' => $this->address,
            'profile_picture' => $this->profile_picture ? asset('storage/' . $this->profile_picture) : null,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Conditional fields based on role
            $this->mergeWhen($this->role === 'broker', [
                'broker_license' => $this->broker_license,
                'broker_status' => $this->broker_status,
                'approved_at' => $this->approved_at,
            ]),
        ];
    }
}