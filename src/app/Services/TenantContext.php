<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Tenant context service.
 *
 * Plan reference: Feature 1.2 — Multi-Tenant (Company/Branch/Warehouse)
 * Plan file: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md
 *
 * Constitution principle #3 (TENANT-ISOLATION):
 *   Every business table carries company_id.
 *   Session key: 'active_company_id', 'active_branch_id', 'active_warehouse_id'
 *
 * The service also applies PostgreSQL's RLS session variable so DB-level
 * policies fire correctly on every query in the request lifecycle.
 */
class TenantContext
{
    public function getCompanyId(): ?int
    {
        return Session::get('active_company_id', Auth::user()?->company_id);
    }

    public function getBranchId(): ?int
    {
        return Session::get('active_branch_id', Auth::user()?->branch_id);
    }

    public function getWarehouseId(): ?int
    {
        return Session::get('active_warehouse_id');
    }

    public function getCompany(): ?Company
    {
        $id = $this->getCompanyId();
        return $id ? Company::find($id) : null;
    }

    public function getBranch(): ?Branch
    {
        $id = $this->getBranchId();
        return $id ? Branch::find($id) : null;
    }

    public function getWarehouse(): ?Warehouse
    {
        $id = $this->getWarehouseId();
        return $id ? Warehouse::find($id) : null;
    }

    /**
     * Switch the active company for the current session.
     * Clears branch and warehouse scopes since they belong to the old company.
     *
     * Plan T1.2-C: Switching company resets warehouse scope.
     */
    public function switchCompany(int $companyId): void
    {
        $user = Auth::user();

        // Verify user has access to this company
        abort_unless(
            $user->company_id === $companyId ||
            ($user->accessibleCompanies ?? collect())->contains($companyId),
            403,
            'You do not have access to this company.'
        );

        Session::put('active_company_id', $companyId);
        Session::forget('active_branch_id');
        Session::forget('active_warehouse_id'); // Plan T1.2-C: reset on company switch

        // Apply PostgreSQL RLS session variable (Constitution principle #3)
        $this->applyRls($companyId);
    }

    public function switchWarehouse(int $warehouseId): void
    {
        $companyId = $this->getCompanyId();

        // Verify warehouse belongs to the active company
        $warehouse = Warehouse::where('id', $warehouseId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        Session::put('active_warehouse_id', $warehouse->id);
        Session::put('active_branch_id', $warehouse->branch_id);
    }

    /**
     * Apply RLS session variable for the current request.
     * Called by TenantScopeMiddleware on every request.
     *
     * Constitution principle #3: PostgreSQL RLS enforces
     * company_id = current_setting('app.current_company_id')
     */
    public function applyRls(?int $companyId = null): void
    {
        // Only apply PostgreSQL RLS variable on pgsql connection
        // (not SQLite for tests — plan: RLS is a PostgreSQL-only feature)
        if (config('database.default') !== 'pgsql') {
            return;
        }

        $id = $companyId ?? $this->getCompanyId();
        if ($id) {
            DB::statement("SET app.current_company_id = ?", [$id]);
        }
    }
}
