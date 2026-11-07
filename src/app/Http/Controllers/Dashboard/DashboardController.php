<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Epc;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockBalance;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please log in to access the dashboard.');
        }

        $companyId = $user->company_id;

        $totalItems = Item::where('company_id', $companyId)->where('is_active', true)->count();

        $stockValue = Stock::where('stock.company_id', $companyId)
            ->sum(DB::raw('quantity_on_hand * unit_cost'));

        $lowStockAlerts = DB::table('items')
            ->join('stock_balances', 'items.id', '=', 'stock_balances.item_id')
            ->select('items.id')
            ->where('items.company_id', $companyId)
            ->where('items.is_active', true)
            ->where('items.reorder_point', '>', 0)
            ->whereNull('items.deleted_at')
            ->groupBy('items.id', 'items.reorder_point')
            ->havingRaw('SUM(stock_balances.total_available) < items.reorder_point')
            ->get()
            ->count();

        $expiringSoon = Batch::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->count();

        $activeEpcs = Epc::where('company_id', $companyId)
            ->whereIn('status', ['active', 'in_use'])
            ->count();

        $stockByWarehouse = StockBalance::where('stock_balances.company_id', $companyId)
            ->join('warehouses', 'stock_balances.warehouse_id', '=', 'warehouses.id')
            ->select('warehouses.name', DB::raw('SUM(total_on_hand) as total'))
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'total' => (float) $row->total]);

        $stockByType = Item::where('items.company_id', $companyId)
            ->where('is_active', true)
            ->select('item_type', DB::raw('COUNT(*) as count'))
            ->groupBy('item_type')
            ->get()
            ->map(fn ($row) => [
                'type' => ucfirst(str_replace('_', ' ', $row->item_type instanceof \BackedEnum ? $row->item_type->value : $row->item_type)),
                'count' => $row->count,
            ]);

        $recentTransactions = InventoryTransaction::where('company_id', $companyId)
            ->with(['item:id,name,sku'])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($txn) => [
                'id' => $txn->id,
                'transaction_number' => $txn->transaction_number,
                'transaction_type' => $txn->transaction_type,
                'item_name' => $txn->item?->name,
                'sku' => $txn->item?->sku,
                'quantity' => $txn->quantity,
                'created_at' => $txn->created_at->toDateTimeString(),
            ]);

        $lowStockItems = Item::where('items.company_id', $companyId)
            ->where('is_active', true)
            ->where('reorder_point', '>', 0)
            ->with(['stockBalances' => fn ($q) => $q->select('item_id', DB::raw('SUM(total_available) as available'))->groupBy('item_id')])
            ->get()
            ->filter(fn ($item) => ($item->stockBalances->first()?->available ?? 0) < $item->reorder_point)
            ->take(10)
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'available' => (float) ($item->stockBalances->first()?->available ?? 0),
                'reorder_point' => $item->reorder_point,
                'min_stock' => $item->min_stock,
            ])
            ->values();

        $expiringBatches = Batch::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->with('item:id,name,sku')
            ->orderBy('expiry_date')
            ->limit(10)
            ->get()
            ->map(fn ($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'item_name' => $batch->item?->name,
                'sku' => $batch->item?->sku,
                'expiry_date' => $batch->expiry_date->toDateString(),
                'days_remaining' => (int) now()->diffInDays($batch->expiry_date, false),
            ]);

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalItems' => $totalItems,
                'stockValue' => round((float) $stockValue, 2),
                'lowStockAlerts' => $lowStockAlerts,
                'expiringSoon' => $expiringSoon,
                'activeEpcs' => $activeEpcs,
            ],
            'stockByWarehouse' => $stockByWarehouse,
            'stockByType' => $stockByType,
            'recentTransactions' => $recentTransactions,
            'lowStockItems' => $lowStockItems,
            'expiringBatches' => $expiringBatches,
        ]);
    }
}
