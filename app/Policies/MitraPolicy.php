<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Mitra;
use Illuminate\Auth\Access\Response;

class MitraPolicy
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
    // private function isAdmin(User $user): bool 
    // {
    //     return $user->is_active && in_array($user->role, ['superadmin', 'admin']);
    // }

    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Mitra $mitra): bool
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
    public function update(User $user, Mitra $mitra): bool
    {
        return $user->is_active && $user->role === 'admin';

    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Mitra $mitra): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Mitra $mitra): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Mitra $mitra): bool
    {
        return false;
    }
}
