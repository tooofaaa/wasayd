<?php

namespace App\Services;

use App\Enums\StockCountStatus;
use App\Models\Stock;
use App\Models\StockCount;
use App\Models\StockCountLine;
use Illuminate\Support\Facades\DB;

class StockCountService
{
    /**
     * Create a new stock count and initialize system quantities
     */
    public function createCount(array $data): StockCount
    {
        return DB::transaction(function () use ($data) {
            $count = StockCount::create([
                'company_id' => auth()->user()->company_id,
                'warehouse_id' => $data['warehouse_id'],
                'count_type' => $data['count_type'],
                'planned_date' => $data['planned_date'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'zone_ids' => $data['zone_ids'] ?? null,
                'category_ids' => $data['category_ids'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => StockCountStatus::Planned,
            ]);

            return $count;
        });
    }

    /**
     * Start the count - transition to in_progress and copy current stock quantities
     */
    public function startCount(StockCount $stockCount): StockCount
    {
        return DB::transaction(function () use ($stockCount) {
            // Get all stock for the warehouse based on filters
            $query = Stock::where('warehouse_id', $stockCount->warehouse_id)
                ->where('quantity_on_hand', '>', 0);

            if ($stockCount->zone_ids) {
                $query->whereHas('bin', function ($q) use ($stockCount) {
                    $q->whereIn('zone_id', $stockCount->zone_ids);
                });
            }

            if ($stockCount->category_ids) {
                $query->whereHas('item', function ($q) use ($stockCount) {
                    $q->whereIn('category_id', $stockCount->category_ids);
                });
            }

            $stocks = $query->with(['item', 'batch', 'bin'])->get();

            // Create count lines with system quantities
            foreach ($stocks as $stock) {
                StockCountLine::create([
                    'company_id' => auth()->user()->company_id,
                    'stock_count_id' => $stockCount->id,
                    'item_id' => $stock->item_id,
                    'batch_id' => $stock->batch_id,
                    'bin_id' => $stock->bin_id,
                    'system_quantity' => $stock->quantity_on_hand,
                ]);
            }

            $stockCount->update([
                'status' => StockCountStatus::InProgress,
                'started_at' => now(),
                'total_items' => $stocks->count(),
            ]);

            return $stockCount->fresh();
        });
    }

    /**
     * Record a counted quantity for a line
     */
    public function recordCountLine(int $countId, array $data): StockCountLine
    {
        $line = StockCountLine::where('stock_count_id', $countId)
            ->where('item_id', $data['item_id'])
            ->where('batch_id', $data['batch_id'] ?? null)
            ->where('bin_id', $data['bin_id'] ?? null)
            ->firstOrFail();

        $line->update([
            'counted_quantity' => $data['counted_quantity'],
            'count_method' => $data['count_method'] ?? 'manual',
            'counted_at' => now(),
            'counted_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Update parent count status
        $stockCount = $line->stockCount;
        if ($stockCount->status === StockCountStatus::InProgress) {
            $stockCount->update(['status' => StockCountStatus::Counting]);
        }

        return $line->fresh();
    }

    /**
     * Submit count for review - calculate totals and variances
     */
    public function submitCount(StockCount $stockCount): StockCount
    {
        return DB::transaction(function () use ($stockCount) {
            $lines = $stockCount->lines()->whereNotNull('counted_quantity')->get();

            $totalCounted = $lines->count();
            $totalVarianceQty = $lines->sum('variance');
            $totalVarianceValue = $lines->sum(fn($l) => ($l->variance ?? 0) * ($l->unit_cost ?? 0));

            $stockCount->update([
                'status' => StockCountStatus::Submitted,
                'total_lines' => $totalCounted,
                'total_variance_qty' => $totalVarianceQty,
                'total_variance_value' => $totalVarianceValue,
            ]);

            return $stockCount->fresh();
        });
    }

    /**
     * Start reviewing the count
     */
    public function reviewCount(StockCount $stockCount): StockCount
    {
        $stockCount->update([
            'status' => StockCountStatus::Reviewing,
            'reviewed_by' => auth()->id(),
        ]);

        return $stockCount->fresh();
    }

    /**
     * Approve the count
     */
    public function approveCount(StockCount $stockCount): StockCount
    {
        $stockCount->update([
            'status' => StockCountStatus::Approved,
            'completed_at' => now(),
        ]);

        return $stockCount->fresh();
    }

    /**
     * Cancel the count
     */
    public function cancelCount(StockCount $stockCount, string $reason): StockCount
    {
        $stockCount->update([
            'status' => StockCountStatus::Cancelled,
            'notes' => ($stockCount->notes ? $stockCount->notes . "\n\n" : '') . "Cancelled: {$reason}",
        ]);

        return $stockCount->fresh();
    }
}
