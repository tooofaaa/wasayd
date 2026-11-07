<?php

namespace App\Services;

use App\Enums\AdjustmentStatus;
use App\Enums\AdjustmentType;
use App\Enums\TransactionType;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentLine;
use App\Models\StockCount;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function __construct(
        protected InventoryTransactionService $transactionService,
        protected StockService $stockService,
        protected ApprovalService $approvalService
    ) {}

    /**
     * Create adjustment from stock count variances
     */
    public function createFromCount(int $stockCountId): StockAdjustment
    {
        return DB::transaction(function () use ($stockCountId) {
            $stockCount = StockCount::with('lines')->findOrFail($stockCountId);

            // Only create adjustment for lines with variance
            $varianceLines = $stockCount->lines()->where('variance', '!=', 0)->get();

            if ($varianceLines->isEmpty()) {
                throw new \RuntimeException('No variances found to adjust');
            }

            $adjustment = StockAdjustment::create([
                'company_id' => auth()->user()->company_id,
                'stock_count_id' => $stockCountId,
                'adjustment_type' => AdjustmentType::CountVariance,
                'status' => AdjustmentStatus::PendingApproval,
                'reason' => "Stock count variance from {$stockCount->count_number}",
            ]);

            $totalQty = 0;
            $totalValue = 0;

            foreach ($varianceLines as $line) {
                StockAdjustmentLine::create([
                    'company_id' => auth()->user()->company_id,
                    'stock_adjustment_id' => $adjustment->id,
                    'item_id' => $line->item_id,
                    'batch_id' => $line->batch_id,
                    'bin_id' => $line->bin_id,
                    'warehouse_id' => $stockCount->warehouse_id,
                    'system_quantity' => $line->system_quantity,
                    'adjusted_quantity' => $line->counted_quantity,
                    'variance' => $line->variance,
                    'reason' => "Count variance: System {$line->system_quantity}, Counted {$line->counted_quantity}",
                ]);

                $totalQty += abs($line->variance);
            }

            $adjustment->update([
                'total_adjustment_qty' => $totalQty,
                'total_adjustment_value' => $totalValue,
            ]);

            return $adjustment->fresh();
        });
    }

    /**
     * Create manual adjustment
     */
    public function createManual(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $adjustment = StockAdjustment::create([
                'company_id' => auth()->user()->company_id,
                'adjustment_type' => $data['adjustment_type'],
                'status' => AdjustmentStatus::PendingApproval,
                'reason' => $data['reason'],
            ]);

            $totalQty = 0;
            $totalValue = 0;

            foreach ($data['lines'] as $lineData) {
                $variance = $lineData['adjusted_quantity'] - $lineData['system_quantity'];

                StockAdjustmentLine::create([
                    'company_id' => auth()->user()->company_id,
                    'stock_adjustment_id' => $adjustment->id,
                    'item_id' => $lineData['item_id'],
                    'batch_id' => $lineData['batch_id'] ?? null,
                    'bin_id' => $lineData['bin_id'] ?? null,
                    'warehouse_id' => $lineData['warehouse_id'],
                    'system_quantity' => $lineData['system_quantity'],
                    'adjusted_quantity' => $lineData['adjusted_quantity'],
                    'variance' => $variance,
                    'reason' => $lineData['reason'] ?? null,
                ]);

                $totalQty += abs($variance);
            }

            $adjustment->update([
                'total_adjustment_qty' => $totalQty,
                'total_adjustment_value' => $totalValue,
            ]);

            return $adjustment->fresh();
        });
    }

    /**
     * Submit adjustment for approval
     */
    public function submitForApproval(StockAdjustment $adjustment): StockAdjustment
    {
        $this->approvalService->submitForApproval($adjustment, 'stock_adjustment');
        return $adjustment->fresh();
    }

    /**
     * Apply approved adjustment - create transactions and update stock
     */
    public function applyAdjustment(StockAdjustment $adjustment): StockAdjustment
    {
        if ($adjustment->status !== AdjustmentStatus::Approved) {
            throw new \RuntimeException('Only approved adjustments can be applied');
        }

        return DB::transaction(function () use ($adjustment) {
            foreach ($adjustment->lines as $line) {
                if ($line->variance == 0) {
                    continue;
                }

                // Create inventory transaction
                $this->transactionService->createTransaction([
                    'company_id' => $adjustment->company_id,
                    'transaction_type' => TransactionType::Adjustment,
                    'item_id' => $line->item_id,
                    'batch_id' => $line->batch_id,
                    'to_warehouse_id' => $line->warehouse_id,
                    'quantity' => $line->variance, // Keep sign to indicate in/out
                    'reference_type' => StockAdjustment::class,
                    'reference_id' => $adjustment->id,
                    'reason' => $adjustment->reason,
                ]);

                // Update stock
                $this->stockService->adjustStock(
                    itemId: $line->item_id,
                    warehouseId: $line->warehouse_id,
                    binId: $line->bin_id,
                    batchId: $line->batch_id,
                    quantity: $line->variance,
                    field: 'quantity_on_hand'
                );
            }

            $adjustment->update([
                'status' => AdjustmentStatus::Applied,
                'applied_at' => now(),
            ]);

            return $adjustment->fresh();
        });
    }
}
