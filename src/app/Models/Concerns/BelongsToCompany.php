<?php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait: BelongsToCompany
 *
 * Plan reference: Feature 1.2 — Tenant isolation
 * Constitution principle #3 (TENANT-ISOLATION):
 *   Every business model uses this trait to auto-scope queries to the
 *   active company. Uses TenantContext (session > auth user fallback).
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        // Global scope: automatically filter by active company
        static::addGlobalScope('company', function (Builder $builder) {
            if ($companyId = static::resolveCompanyId()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.company_id',
                    $companyId
                );
            }
        });

        // Auto-assign company_id on create
        static::creating(function (Model $model) {
            if (! $model->company_id && $companyId = static::resolveCompanyId()) {
                $model->company_id = $companyId;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Resolve company_id from TenantContext (session-based, then user fallback).
     * Plan T1.2-A: Cross-tenant isolation — this is the enforcement point.
     */
    protected static function resolveCompanyId(): ?int
    {
        // Use TenantContext if the service container is available
        if (app()->bound(TenantContext::class)) {
            return app(TenantContext::class)->getCompanyId();
        }

        // Fallback for testing without full container
        return auth()->user()?->company_id;
    }
}
