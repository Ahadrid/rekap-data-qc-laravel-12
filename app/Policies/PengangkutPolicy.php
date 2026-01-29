<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pengangkut;
use Illuminate\Auth\Access\Response;

class PengangkutPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if($user->role === 'superadmin') {
            return true;
        }
        return null;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pengangkut $pengangkut): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pengangkut $pengangkut): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pengangkut $pengangkut): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pengangkut $pengangkut): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pengangkut $pengangkut): bool
    {
        return false;
    }
}
