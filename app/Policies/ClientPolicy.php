<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view any clients.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'broker']);
    }

    /**
     * Determine whether the user can view the client.
     */
    public function view(User $user, Client $client): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'broker') {
            return $client->broker_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create clients.
     */
    public function create(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'broker') {
            return $user->is_approved && $user->application_status === 'approved';
        }
        
        return false;
    }

    /**
     * Determine whether the user can update the client.
     */
    public function update(User $user, Client $client): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'broker') {
            return $user->is_approved && 
                   $user->application_status === 'approved' && 
                   $client->broker_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the client.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->role === 'admin';
    }
}