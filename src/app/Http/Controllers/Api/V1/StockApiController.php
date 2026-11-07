<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StockBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 25);
        $warehouseId = $request->input('warehouse_id');
        $itemId = $request->input('item_id');
        $lowStock = $request->boolean('low_stock');

        $stocks = StockBalance::query()
            ->with([
                'item:id,name,name_ar,sku,min_stock,reorder_point',
                'warehouse:id,name,name_ar',
            ])
            ->when($warehouseId, fn($q, $id) => $q->where('warehouse_id', $id))
            ->when($itemId, fn($q, $id) => $q->where('item_id', $id))
            ->when($lowStock, function ($q) {
                $q->whereHas('item', function ($sq) {
                    $sq->whereColumn('stock_balances.total_available', '<', 'items.min_stock');
                });
            })
            ->orderBy('warehouse_id')
            ->orderBy('item_id')
            ->paginate($perPage);

        return response()->json($stocks);
    }
}
