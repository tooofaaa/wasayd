<?php

namespace App\Providers;

use App\Http\Middleware\TenantScopeMiddleware;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Plan reference: Feature 1.2 — Multi-Tenant setup
 * Constitution principle #3 (TENANT-ISOLATION)
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton TenantContext — plan F1.2: session-based tenant isolation
        $this->app->singleton(TenantContext::class, fn () => new TenantContext());
    }

    public function boot(): void
    {
        // Super admin bypass
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
