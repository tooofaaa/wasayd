<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: set active tenant scope on every authenticated request.
 *
 * Plan reference: Feature 1.2 — Tenant isolation
 * Constitution principle #3 (TENANT-ISOLATION):
 *   Sets PostgreSQL RLS session variable so DB-level policies fire.
 *   Must run after 'auth' middleware.
 */
class TenantScopeMiddleware
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            // Apply PostgreSQL RLS variable for the active company
            $this->tenantContext->applyRls();
        }

        return $next($request);
    }
}
