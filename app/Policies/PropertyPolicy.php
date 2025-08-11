<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view properties
    }

    public function view(User $user, Property $property): bool
    {
        return true; // All authenticated users can view individual properties
    }

    public function create(User $user): bool
    {
        // Only brokers can create properties, not admins
        return $user->role === 'broker' && $user->is_approved;
    }

    public function update(User $user, Property $property): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'broker' 
            && $user->is_approved 
            && $property->broker_id === $user->id;
    }

    public function delete(User $user, Property $property): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'broker' 
            && $user->is_approved 
            && $property->broker_id === $user->id;
    }
}
