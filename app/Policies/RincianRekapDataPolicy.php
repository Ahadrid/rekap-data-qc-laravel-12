<?php

namespace App\Policies;

use App\Models\RekapData;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RincianRekapDataPolicy
{
    /**
     * Determine whether the user can view any models.
     */

    public function before(User $user, string $ability): ?bool
    {
        if($user->role === 'superadmin') {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RekapData $rekapData): bool
    {
        return $user->is_active && $user->role === 'admin';
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
    public function update(User $user, RekapData $rekapData): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RekapData $rekapData): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RekapData $rekapData): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RekapData $rekapData): bool
    {
        return $user->is_active && $user->role === 'admin';
    }
}
