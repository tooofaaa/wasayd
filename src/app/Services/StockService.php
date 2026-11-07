<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockBalance;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Adjust stock quantity for a specific location and batch
     * Note: quantity_available is a generated stored column - DO NOT set it manually
     */
    public function adjustStock(
        int $itemId,
        int $warehouseId,
        ?int $binId = null,
        ?int $batchId = null,
        float $quantity = 0,
        string $field = 'quantity_on_hand'
    ): Stock {
        $companyId = auth()->user()->company_id;

        $stock = Stock::firstOrCreate(
            [
                'company_id' => $companyId,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'bin_id' => $binId,
                'batch_id' => $batchId,
            ],
            [
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'quantity_in_transit' => 0,
                'unit_cost' => 0,
            ]
        );

        // Increment or decrement the specified field
        $stock->increment($field, $quantity);

        return $stock->fresh();
    }

    /**
     * Recalculate stock balance by aggregating all stock records
     */
    public function recalculateBalance(int $itemId, int $warehouseId): StockBalance
    {
        $companyId = auth()->user()->company_id;

        $aggregates = Stock::where('company_id', $companyId)
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->select([
                DB::raw('SUM(quantity_on_hand) as total_on_hand'),
                DB::raw('SUM(quantity_reserved) as total_reserved'),
                DB::raw('SUM(quantity_available) as total_available'),
                DB::raw('SUM(quantity_in_transit) as total_in_transit'),
                DB::raw('AVG(unit_cost) as weighted_avg_cost'),
            ])
            ->first();

        $totalOnHand = $aggregates->total_on_hand ?? 0;
        $totalReserved = $aggregates->total_reserved ?? 0;
        $totalAvailable = $aggregates->total_available ?? 0;
        $totalInTransit = $aggregates->total_in_transit ?? 0;
        $weightedAvgCost = $aggregates->weighted_avg_cost ?? 0;

        return StockBalance::updateOrCreate(
            [
                'company_id' => $companyId,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'total_on_hand' => $totalOnHand,
                'total_reserved' => $totalReserved,
                // total_available is a MySQL GENERATED STORED column — omit here
                'total_in_transit' => $totalInTransit,
                'weighted_avg_cost' => $weightedAvgCost,
            ]
        );
    }
}
