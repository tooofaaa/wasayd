<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 25);
        $itemId = $request->input('item_id');
        $warehouseId = $request->input('warehouse_id');
        $transactionType = $request->input('transaction_type');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $transactions = InventoryTransaction::query()
            ->with([
                'item:id,name,name_ar,sku',
                'warehouse:id,name,name_ar',
                'user:id,name',
            ])
            ->when($itemId, fn($q, $id) => $q->where('item_id', $id))
            ->when($warehouseId, fn($q, $id) => $q->where('warehouse_id', $id))
            ->when($transactionType, fn($q, $type) => $q->where('transaction_type', $type))
            ->when($dateFrom, fn($q, $date) => $q->whereDate('transaction_date', '>=', $date))
            ->when($dateTo, fn($q, $date) => $q->whereDate('transaction_date', '<=', $date))
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($transactions);
    }

    public function show(InventoryTransaction $transaction): JsonResponse
    {
        $transaction->load([
            'item:id,name,name_ar,sku',
            'warehouse:id,name,name_ar',
            'user:id,name',
            'batch:id,batch_number',
        ]);

        return response()->json([
            'data' => $transaction,
        ]);
    }
}
