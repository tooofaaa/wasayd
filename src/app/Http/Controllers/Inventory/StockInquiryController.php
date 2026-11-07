<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\ItemType;
use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockInquiryController extends Controller
{
    /**
     * Display stock inquiry with complex filtering
     */
    public function index(Request $request): Response
    {
        $query = Stock::query()
            ->with([
                'item.category',
                'item.baseUom',
                'warehouse',
                'zone',
                'bin.shelf.rack.aisle.zone',
                'batch',
            ])
            ->where('company_id', auth()->user()->company_id);

        // Filter by item
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by zone
        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        // Filter by bin
        if ($request->filled('bin_id')) {
            $query->where('bin_id', $request->bin_id);
        }

        // Filter by batch
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        // Filter by item type
        if ($request->filled('item_type')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('item_type', $request->item_type);
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Search by SKU or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by stock availability
        if ($request->filled('availability')) {
            match ($request->availability) {
                'available' => $query->where('quantity_available', '>', 0),
                'low_stock' => $query->whereHas('item', function ($q) {
                    $q->whereColumn('stock.quantity_on_hand', '<', 'items.min_stock');
                }),
                'out_of_stock' => $query->where('quantity_on_hand', '<=', 0),
                default => null,
            };
        }

        // Only show records with positive on-hand quantity by default
        if (!$request->filled('show_zero_stock')) {
            $query->where('quantity_on_hand', '>', 0);
        }

        $stock = $query->latest('updated_at')->paginate(20)->withQueryString();

        // Get filter options
        $warehouses = Warehouse::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->select('id', 'name', 'code')
            ->get();

        $categories = ItemCategory::where('company_id', auth()->user()->company_id)
            ->select('id', 'name')
            ->get();

        $itemTypes = collect(ItemType::cases())->map(fn ($type) => [
            'value' => $type->value,
            'label' => ucfirst(str_replace('_', ' ', $type->value)),
        ]);

        return Inertia::render('Inventory/Stock/Index', [
            'stocks' => $stock,
            'filters' => $request->only([
                'item_id', 'warehouse_id', 'zone_id', 'bin_id', 'batch_id',
                'item_type', 'category_id', 'search', 'availability', 'show_zero_stock'
            ]),
            'warehouses' => $warehouses,
            'categories' => $categories,
            'itemTypes' => $itemTypes,
            'availabilityOptions' => [
                ['value' => 'available', 'label' => 'Available'],
                ['value' => 'low_stock', 'label' => 'Low Stock'],
                ['value' => 'out_of_stock', 'label' => 'Out of Stock'],
            ],
        ]);
    }
}
