<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Epc;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockBalance;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard Controller — WASAYD
 *
 * Plan reference: Feature 1.6 — Dashboard Shell + KPI Widgets
 * Plan T1.6-A: stats.inventory, stats.rfid, stats.transactions groups
 * Plan T1.6-B: Stats scoped to active warehouse
 * Constitution principle #3: Tenant-isolated stats
 * Constitution principle #8: Arabic-first (labels come from i18n, not hardcoded)
 */
class DashboardController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function index(): Response
    {
        $user        = auth()->user();
        $companyId   = $this->tenantContext->getCompanyId();
        $warehouseId = $this->tenantContext->getWarehouseId();

        // Cache stats for 60s per company + warehouse scope
        $cacheKey = "dashboard_stats:{$companyId}:" . ($warehouseId ?? 'all');

        $stats = Cache::remember($cacheKey, 60, function () use ($companyId, $warehouseId) {
            return [
                'inventory'    => $this->inventoryKpis($companyId, $warehouseId),
                'rfid'         => $this->rfidKpis($companyId, $warehouseId),
                'transactions' => $this->transactionKpis($companyId, $warehouseId),
            ];
        });

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Inventory KPIs.
     * Plan: Total Items, Total Stock, Low Stock, Out of Stock, Expiring Soon, Expired.
     */
    private function inventoryKpis(int $companyId, ?int $warehouseId): array
    {
        $stockQuery = StockBalance::where('company_id', $companyId);
        if ($warehouseId) {
            $stockQuery->where('warehouse_id', $warehouseId);
        }

        $totalStockQty = (int) $stockQuery->sum('total_on_hand');

        $itemQuery = Item::where('company_id', $companyId)->where('is_active', true);
        $totalItems = $itemQuery->count();

        // Low stock: on-hand < reorder_point
        $lowStock = Item::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('reorder_point', '>', 0)
            ->whereHas('stockBalances', function ($q) use ($warehouseId) {
                $q->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                  ->havingRaw('SUM(total_available) < items.reorder_point')
                  ->groupBy('item_id');
            })
            ->count();

        $outOfStock = Item::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereDoesntHave('stockBalances', fn ($q) =>
                $q->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                  ->where('total_available', '>', 0)
            )->count();

        $expiringSoon = Batch::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(90))
            ->count();

        $expiredItems = Batch::where('company_id', $companyId)
            ->where('expiry_date', '<', now())
            ->count();

        return [
            'total_items'   => $totalItems,
            'total_stock'   => $totalStockQty,
            'low_stock'     => $lowStock,
            'out_of_stock'  => $outOfStock,
            'expiring_soon' => $expiringSoon,
            'expired_items' => $expiredItems,
        ];
    }

    /**
     * RFID KPIs.
     * Plan: Total EPCs, Encoded, In Stock, Not Found, Location Variance.
     */
    private function rfidKpis(int $companyId, ?int $warehouseId): array
    {
        $epcQuery = Epc::where('company_id', $companyId);
        if ($warehouseId) {
            $epcQuery->where('warehouse_id', $warehouseId);
        }

        return [
            'total_epcs'           => (clone $epcQuery)->count(),
            'encoded_epcs'         => (clone $epcQuery)->where('status', 'encoded')->count(),
            'epcs_in_stock'        => (clone $epcQuery)->where('status', 'active')->count(),
            'epcs_not_found'       => (clone $epcQuery)->where('status', 'missing')->count(),
            'epcs_location_variance' => (clone $epcQuery)->where('status', 'location_variance')->count(),
        ];
    }

    /**
     * Transaction KPIs — today's counts.
     * Plan: Today's Receipts, Issues, Transfers, Adjustments, Stock Counts.
     */
    private function transactionKpis(int $companyId, ?int $warehouseId): array
    {
        $today = now()->toDateString();
        $txQuery = InventoryTransaction::where('company_id', $companyId)
            ->whereDate('created_at', $today);

        if ($warehouseId) {
            $txQuery->where('warehouse_id', $warehouseId);
        }

        $typeCounts = (clone $txQuery)
            ->select('transaction_type', DB::raw('COUNT(*) as count'))
            ->groupBy('transaction_type')
            ->pluck('count', 'transaction_type');

        return [
            'today_receipts'    => (int) ($typeCounts['receiving'] ?? 0),
            'today_issues'      => (int) ($typeCounts['issue'] ?? 0),
            'today_transfers'   => (int) ($typeCounts['transfer_out'] ?? 0),
            'today_adjustments' => (int) ($typeCounts['adjustment'] ?? 0),
            'today_counts'      => (int) ($typeCounts['stock_count'] ?? 0),
        ];
    }
}
