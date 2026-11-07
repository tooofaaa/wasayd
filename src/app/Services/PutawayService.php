<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Bin;
use App\Models\PutawayLine;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class PutawayService
{
    public function __construct(
        private InventoryTransactionService $transactionService,
        private StockService $stockService
    ) {}

    /**
     * Suggest a location for putaway (simple: first available bin)
     */
    public function suggestLocation(int $itemId, int $warehouseId): ?Bin
    {
        // Get the warehouse's zones, then find bins
        // For simplicity, find first available (not occupied) bin
        $bin = Bin::whereHas('shelf.rack.aisle.zone', function ($query) use ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        })
        ->where('is_active', true)
        ->where('is_occupied', false)
        ->first();

        return $bin;
    }

    /**
     * Confirm putaway by moving stock to a specific bin
     */
    public function confirmPutaway(PutawayLine $line, int $toBinId): PutawayLine
    {
        return DB::transaction(function () use ($line, $toBinId) {
            $putaway = $line->putaway;

            // Find the stock record (bin_id will be null from receiving)
            $stock = Stock::where('company_id', auth()->user()->company_id)
                ->where('item_id', $line->item_id)
                ->where('warehouse_id', $putaway->warehouse_id)
                ->where('batch_id', $line->batch_id)
                ->whereNull('bin_id')
                ->first();

            if (!$stock) {
                throw new \RuntimeException('Stock record not found for putaway.');
            }

            // Create transaction
            $this->transactionService->createTransaction([
                'company_id' => auth()->user()->company_id,
                'transaction_type' => TransactionType::Putaway,
                'item_id' => $line->item_id,
                'batch_id' => $line->batch_id,
                'to_warehouse_id' => $putaway->warehouse_id,
                'quantity' => $line->quantity,
                'reference_type' => PutawayLine::class,
                'reference_id' => $line->id,
                'notes' => "Putaway to bin",
            ]);

            // Update stock bin_id
            $stock->update(['bin_id' => $toBinId]);

            // Update putaway line status
            $line->update([
                'to_bin_id' => $toBinId,
                'status' => 'placed',
                'placed_at' => now(),
            ]);

            // Mark bin as occupied
            $bin = Bin::find($toBinId);
            if ($bin) {
                $bin->update(['is_occupied' => true]);
            }

            return $line->fresh();
        });
    }
}
