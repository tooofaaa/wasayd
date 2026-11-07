<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Epc;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockBalance;
use App\Models\StockCount;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }

    public function stockSummary(): Response
    {
        $warehouseId = request('warehouse_id');
        $categoryId = request('category_id');
        $itemType = request('item_type');

        $stocks = StockBalance::query()
            ->with([
                'item:id,name,name_ar,sku,category_id,item_type',
                'item.category:id,name,name_ar',
                'warehouse:id,name,name_ar',
            ])
            ->when($warehouseId, fn($q, $id) => $q->where('warehouse_id', $id))
            ->when($categoryId, fn($q, $id) => $q->whereHas('item', fn($sq) => $sq->where('category_id', $id)))
            ->when($itemType, fn($q, $type) => $q->whereHas('item', fn($sq) => $sq->where('item_type', $type)))
            ->orderBy('warehouse_id')
            ->orderBy('item_id')
            ->get()
            ->map(fn($stock) => [
                'item_name' => $stock->item->name,
                'item_name_ar' => $stock->item->name_ar,
                'sku' => $stock->item->sku,
                'category' => $stock->item->category?->name,
                'warehouse' => $stock->warehouse->name,
                'on_hand' => $stock->total_on_hand,
                'reserved' => $stock->total_reserved,
                'available' => $stock->total_available,
                'item_type' => $stock->item->item_type->label(),
            ]);

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->orderBy('name')->get();

        $chartData = StockBalance::query()
            ->with('warehouse:id,name')
            ->selectRaw('warehouse_id, SUM(total_available) as total')
            ->groupBy('warehouse_id')
            ->get()
            ->map(fn($row) => [
                'warehouse' => $row->warehouse->name,
                'total' => (float) $row->total,
            ]);

        return Inertia::render('Reports/StockSummary', [
            'stocks' => $stocks,
            'warehouses' => $warehouses,
            'chartData' => $chartData,
            'filters' => [
                'warehouse_id' => $warehouseId,
                'category_id' => $categoryId,
                'item_type' => $itemType,
            ],
        ]);
    }

    public function stockMovement(): Response
    {
        $dateFrom = request('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = request('date_to', now()->format('Y-m-d'));
        $warehouseId = request('warehouse_id');
        $itemType = request('item_type');

        $movements = InventoryTransaction::query()
            ->with([
                'item:id,name,name_ar,sku,item_type',
                'toWarehouse:id,name,name_ar',
                'fromWarehouse:id,name,name_ar',
            ])
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->when($warehouseId, fn($q, $id) => $q->where('to_warehouse_id', $id)->orWhere('from_warehouse_id', $id))
            ->when($itemType, fn($q, $type) => $q->whereHas('item', fn($sq) => $sq->where('item_type', $type)))
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($txn) => [
                'date' => $txn->transaction_date->format('Y-m-d'),
                'item_name' => $txn->item?->name,
                'sku' => $txn->item?->sku,
                'warehouse' => $txn->toWarehouse?->name ?? $txn->fromWarehouse?->name ?? '-',
                'type' => $txn->transaction_type,
                'quantity_in' => $txn->quantity > 0 ? $txn->quantity : 0,
                'quantity_out' => $txn->quantity < 0 ? abs($txn->quantity) : 0,
                'reference' => $txn->transaction_number,
            ]);

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->orderBy('name')->get();

        return Inertia::render('Reports/StockMovement', [
            'movements' => $movements,
            'warehouses' => $warehouses,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'warehouse_id' => $warehouseId,
                'item_type' => $itemType,
            ],
        ]);
    }

    public function stockValuation(): Response
    {
        $warehouseId = request('warehouse_id');

        $valuations = StockBalance::query()
            ->with([
                'item:id,name,name_ar,sku,unit_cost',
                'warehouse:id,name,name_ar',
            ])
            ->when($warehouseId, fn($q, $id) => $q->where('warehouse_id', $id))
            ->get()
            ->map(fn($stock) => [
                'item_name' => $stock->item->name,
                'sku' => $stock->item->sku,
                'warehouse' => $stock->warehouse->name,
                'quantity' => $stock->total_available,
                'unit_cost' => (float) $stock->item->unit_cost,
                'total_value' => $stock->total_available * (float) $stock->item->unit_cost,
            ]);

        $totalValue = $valuations->sum('total_value');

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->orderBy('name')->get();

        return Inertia::render('Reports/StockValuation', [
            'valuations' => $valuations,
            'totalValue' => $totalValue,
            'warehouses' => $warehouses,
            'filters' => ['warehouse_id' => $warehouseId],
        ]);
    }

    public function expiryReport(): Response
    {
        $daysAhead = request('days_ahead', 30);

        $expiringBatches = Batch::query()
            ->with(['item:id,name,name_ar,sku'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($daysAhead))
            ->where('status', 'active')
            ->orderBy('expiry_date')
            ->get()
            ->map(fn($batch) => [
                'item_name' => $batch->item?->name,
                'sku' => $batch->item?->sku,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days_remaining' => (int) now()->diffInDays($batch->expiry_date, false),
                'lot_number' => $batch->lot_number,
                'urgency' => $this->getExpiryUrgency((int) now()->diffInDays($batch->expiry_date, false)),
            ]);

        return Inertia::render('Reports/Expiry', [
            'batches' => $expiringBatches,
            'filters' => ['days_ahead' => $daysAhead],
        ]);
    }

    public function lowStock(): Response
    {
        $categoryId = request('category_id');
        $itemType = request('item_type');

        $lowStockItems = Item::query()
            ->with([
                'category:id,name,name_ar',
                'stockBalances' => fn($q) => $q->select('item_id', DB::raw('SUM(total_available) as total_stock')),
            ])
            ->where('is_active', true)
            ->when($categoryId, fn($q, $id) => $q->where('category_id', $id))
            ->when($itemType, fn($q, $type) => $q->where('item_type', $type))
            ->whereHas('stockBalances')
            ->get()
            ->map(function ($item) {
                $totalStock = $item->stockBalances->sum('total_stock');
                $reorderPoint = $item->reorder_point ?? $item->min_stock ?? 0;

                return [
                    'item_name' => $item->name,
                    'sku' => $item->sku,
                    'category' => $item->category?->name,
                    'current_stock' => $totalStock,
                    'min_stock' => $item->min_stock,
                    'reorder_point' => $reorderPoint,
                    'shortfall' => max(0, $reorderPoint - $totalStock),
                    'status' => $totalStock <= 0 ? 'out_of_stock' : 'low_stock',
                ];
            })
            ->filter(fn($item) => $item['current_stock'] < $item['reorder_point'])
            ->values();

        return Inertia::render('Reports/LowStock', [
            'items' => $lowStockItems,
            'filters' => [
                'category_id' => $categoryId,
                'item_type' => $itemType,
            ],
        ]);
    }

    public function transactionHistory(): Response
    {
        $dateFrom = request('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = request('date_to', now()->format('Y-m-d'));
        $transactionType = request('transaction_type');
        $warehouseId = request('warehouse_id');

        $transactions = InventoryTransaction::query()
            ->with([
                'item:id,name,name_ar,sku',
                'toWarehouse:id,name,name_ar',
                'createdBy:id,name',
            ])
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->when($transactionType, fn($q, $type) => $q->where('transaction_type', $type))
            ->when($warehouseId, fn($q, $id) => $q->where('to_warehouse_id', $id)->orWhere('from_warehouse_id', $id))
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->through(fn($txn) => [
                'id' => $txn->id,
                'date' => $txn->transaction_date->format('Y-m-d H:i'),
                'type' => $txn->transaction_type,
                'item_name' => $txn->item?->name,
                'sku' => $txn->item?->sku,
                'warehouse' => $txn->toWarehouse?->name ?? '-',
                'quantity' => $txn->quantity,
                'reference' => $txn->transaction_number,
                'user' => $txn->createdBy?->name ?? '-',
                'notes' => $txn->reason,
            ]);

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->orderBy('name')->get();

        return Inertia::render('Reports/TransactionHistory', [
            'transactions' => $transactions,
            'warehouses' => $warehouses,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'transaction_type' => $transactionType,
                'warehouse_id' => $warehouseId,
            ],
        ]);
    }

    public function epcStatus(): Response
    {
        $status = request('status');

        $epcSummary = Epc::query()
            ->selectRaw('item_id, status, COUNT(*) as count')
            ->groupBy('item_id', 'status')
            ->with('item:id,name,name_ar,sku')
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => [
                'item_name' => $group->first()->item->name,
                'sku' => $group->first()->item->sku,
                'total_epcs' => $group->sum('count'),
                'active' => $group->where('status', 'active')->sum('count'),
                'in_use' => $group->where('status', 'in_use')->sum('count'),
                'decommissioned' => $group->where('status', 'decommissioned')->sum('count'),
            ])
            ->values();

        return Inertia::render('Reports/EpcStatus', [
            'summary' => $epcSummary,
            'filters' => ['status' => $status],
        ]);
    }

    public function assetUtilization(): Response
    {
        $department = request('department');
        $status = request('status');

        $assets = Asset::query()
            ->when($department, fn($q, $dept) => $q->where('department', $dept))
            ->when($status, fn($q, $st) => $q->where('status', $st))
            ->selectRaw('status, department, COUNT(*) as count')
            ->groupBy('status', 'department')
            ->get();

        $utilization = $assets->map(fn($asset) => [
            'department' => $asset->department,
            'status' => $asset->status,
            'count' => $asset->count,
        ]);

        $totalAssets = Asset::count();
        $inUseAssets = Asset::where('status', 'in_use')->count();
        $utilizationRate = $totalAssets > 0 ? round(($inUseAssets / $totalAssets) * 100, 2) : 0;

        return Inertia::render('Reports/AssetUtilization', [
            'utilization' => $utilization,
            'utilizationRate' => $utilizationRate,
            'filters' => [
                'department' => $department,
                'status' => $status,
            ],
        ]);
    }

    public function maintenanceDue(): Response
    {
        $filter = request('filter', 'all'); // all, due, overdue

        $maintenances = AssetMaintenance::query()
            ->with([
                'asset:id,name,asset_code',
            ])
            ->when($filter === 'due', fn($q) => $q->whereDate('scheduled_date', '>=', now())
                ->whereDate('scheduled_date', '<=', now()->addDays(30)))
            ->when($filter === 'overdue', fn($q) => $q->whereDate('scheduled_date', '<', now())
                ->whereNull('completed_date'))
            ->whereNull('completed_date')
            ->orderBy('scheduled_date')
            ->get()
            ->map(fn($maint) => [
                'asset_name' => $maint->asset->name,
                'asset_code' => $maint->asset->asset_code,
                'maintenance_type' => $maint->maintenance_type,
                'scheduled_date' => $maint->scheduled_date->format('Y-m-d'),
                'days_until_due' => now()->diffInDays($maint->scheduled_date, false),
                'urgency' => now()->isAfter($maint->scheduled_date) ? 'overdue' : 'due',
                'description' => $maint->description,
            ]);

        return Inertia::render('Reports/MaintenanceDue', [
            'maintenances' => $maintenances,
            'filters' => ['filter' => $filter],
        ]);
    }

    public function controlledMedReconciliation(): Response
    {
        $dateFrom = request('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = request('date_to', now()->format('Y-m-d'));
        $itemId = request('item_id');

        $controlledItems = Item::query()
            ->where('is_controlled', true)
            ->when($itemId, fn($q, $id) => $q->where('id', $id))
            ->with(['stockBalances'])
            ->get()
            ->map(function ($item) use ($dateFrom, $dateTo) {
                $openingBalance = $this->getControlledMedBalance($item->id, $dateFrom);
                $received = $this->getControlledMedReceived($item->id, $dateFrom, $dateTo);
                $dispensed = $this->getControlledMedDispensed($item->id, $dateFrom, $dateTo);
                $closingBalance = $item->stockBalances->sum('total_available');

                return [
                    'item_name' => $item->name,
                    'sku' => $item->sku,
                    'opening_balance' => $openingBalance,
                    'received' => $received,
                    'dispensed' => $dispensed,
                    'closing_balance' => $closingBalance,
                    'calculated_balance' => $openingBalance + $received - $dispensed,
                    'variance' => $closingBalance - ($openingBalance + $received - $dispensed),
                ];
            });

        return Inertia::render('Reports/ControlledMed', [
            'reconciliation' => $controlledItems,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'item_id' => $itemId,
            ],
        ]);
    }

    public function auditTrailReport(): Response
    {
        $dateFrom = request('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = request('date_to', now()->format('Y-m-d'));
        $userId = request('user_id');
        $eventType = request('event_type');

        $auditLogs = AuditLog::query()
            ->with('user:id,name')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($userId, fn($q, $id) => $q->where('user_id', $id))
            ->when($eventType, fn($q, $type) => $q->where('event', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->through(fn($log) => [
                'id' => $log->id,
                'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
                'user' => $log->user?->name ?? 'System',
                'event' => $log->event,
                'auditable_type' => class_basename($log->auditable_type),
                'auditable_id' => $log->auditable_id,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'ip_address' => $log->ip_address,
            ]);

        return Inertia::render('Reports/AuditTrail', [
            'logs' => $auditLogs,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'user_id' => $userId,
                'event_type' => $eventType,
            ],
        ]);
    }

    public function countAccuracy(): Response
    {
        $countDate = request('count_date');

        $counts = StockCount::query()
            ->with([
                'warehouse:id,name,name_ar',
                'lines.item:id,name,name_ar,sku',
            ])
            ->when($countDate, fn($q, $date) => $q->whereDate('count_date', $date))
            ->where('status', 'completed')
            ->get()
            ->flatMap(fn($count) => $count->lines->map(fn($line) => [
                'count_date' => $count->count_date->format('Y-m-d'),
                'warehouse' => $count->warehouse->name,
                'item_name' => $line->item->name,
                'sku' => $line->item->sku,
                'system_qty' => $line->system_qty,
                'counted_qty' => $line->counted_qty,
                'variance' => $line->variance_qty,
                'variance_pct' => $line->system_qty > 0
                    ? round(($line->variance_qty / $line->system_qty) * 100, 2)
                    : 0,
            ]));

        $totalVariance = $counts->sum('variance');
        $avgAccuracy = $counts->count() > 0
            ? round(100 - ($counts->avg('variance_pct')), 2)
            : 100;

        return Inertia::render('Reports/CountAccuracy', [
            'counts' => $counts,
            'totalVariance' => $totalVariance,
            'avgAccuracy' => $avgAccuracy,
            'filters' => ['count_date' => $countDate],
        ]);
    }

    private function getExpiryUrgency(int $daysRemaining): string
    {
        if ($daysRemaining <= 7) return 'critical';
        if ($daysRemaining <= 15) return 'urgent';
        if ($daysRemaining <= 30) return 'warning';
        return 'normal';
    }

    private function getControlledMedBalance(int $itemId, string $date): float
    {
        return InventoryTransaction::where('item_id', $itemId)
            ->whereDate('transaction_date', '<', $date)
            ->sum('quantity');
    }

    private function getControlledMedReceived(int $itemId, string $dateFrom, string $dateTo): float
    {
        return InventoryTransaction::where('item_id', $itemId)
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->where('quantity', '>', 0)
            ->sum('quantity');
    }

    private function getControlledMedDispensed(int $itemId, string $dateFrom, string $dateTo): float
    {
        return abs(InventoryTransaction::where('item_id', $itemId)
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->where('quantity', '<', 0)
            ->sum('quantity'));
    }
}
