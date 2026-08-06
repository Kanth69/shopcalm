<?php

namespace App\Policies;

use App\Models\User;

class AdminProfilePolicy
{
    /**
     * Determine whether the user can view the profile.
     */
    public function view(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the profile.
     */
    public function update(User $user): bool
    {
        return $user->isAdmin();
    }
}
