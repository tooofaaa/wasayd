<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

trait AuthorizesRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Checks Spatie Permission-based access using Gate.
     * Falls back to company-scoped access: user must belong to the same company
     * as the resource being accessed (when company_id is present on the model).
     */
    protected function authorizePermission(string $permission): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->can($permission);
    }

    /**
     * Determine if the user is authorized to access a company-scoped resource.
     *
     * The user must have the given permission AND the resource must belong
     * to the user's company (multi-tenant enforcement per Constitution I).
     */
    protected function authorizeCompanyScoped(string $permission, int $companyId): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (! $user->can($permission)) {
            return false;
        }

        return $user->company_id === $companyId;
    }
}
