<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

/**
 * Inertia middleware — shares tenant context on every page load.
 *
 * Plan reference: Feature 1.2 — Multi-Tenant, Inertia shared data
 * Plan T1.2-D: Inertia shared data contains auth.user, company, branch, warehouse
 * Constitution principle #3: Tenant-scoped data on every page.
 */
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(private TenantContext $tenantContext) {}

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            // Auth — includes abilities for Vue permission checks (Plan T1.4-C)
            'auth' => fn () => $user ? [
                'user' => [
                    ...$user->only('id', 'name', 'name_ar', 'email', 'locale', 'theme'),
                    'company_id'  => $user->company_id,
                    'branch_id'   => $user->branch_id,
                    'roles'       => $user->roles->pluck('name'),
                    // 'abilities' flattened to array for Vue can() helper
                    'abilities'   => $user->getAllPermissions()->pluck('name')->toArray(),
                ],
            ] : ['user' => null],

            // Active tenant context — Plan T1.2-D
            'company'   => fn () => $this->tenantContext->getCompany()?->only(
                'id', 'name', 'name_ar', 'code', 'currency', 'locale', 'timezone'
            ),
            'branch'    => fn () => $this->tenantContext->getBranch()?->only(
                'id', 'name', 'name_ar', 'code', 'timezone'
            ),
            'warehouse' => fn () => $this->tenantContext->getWarehouse()?->only(
                'id', 'name', 'name_ar', 'code', 'warehouse_type'
            ),

            // i18n / locale
            'locale'    => app()->getLocale(),

            // Flash messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],

            // Ziggy route helpers
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
