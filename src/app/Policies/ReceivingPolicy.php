<?php

namespace App\Policies;

use App\Models\Receiving;
use App\Models\User;

class ReceivingPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Receiving $receiving): bool
    {
        return $user->company_id === $receiving->company_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Receiving $receiving): bool
    {
        return $user->company_id === $receiving->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Receiving $receiving): bool
    {
        return $user->company_id === $receiving->company_id;
    }
}
