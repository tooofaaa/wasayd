<?php

namespace App\Services;

use App\Enums\RecallLineStatus;
use App\Enums\RecallStatus;
use App\Models\Recall;
use App\Models\RecallLine;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class RecallService
{
    public function __construct(
        private QuarantineService $quarantineService
    ) {}

    /**
     * Initiate a recall and identify all affected stock
     */
    public function initiateRecall(array $data): Recall
    {
        return DB::transaction(function () use ($data) {
            $companyId = auth()->user()->company_id;

            // Create recall record
            $recall = Recall::create([
                'company_id' => $companyId,
                'item_id' => $data['item_id'],
                'recall_class' => $data['recall_class'],
                'manufacturer_recall_id' => $data['manufacturer_recall_id'] ?? null,
                'status' => RecallStatus::Initiated,
                'description' => $data['description'],
                'instructions' => $data['instructions'] ?? null,
                'affected_batches' => $data['affected_batches'] ?? null,
                'initiated_by' => auth()->id(),
                'initiated_date' => now()->toDateString(),
            ]);

            // Scan all warehouses for affected batches
            $affectedStock = $this->identifyAffectedStock(
                $data['item_id'],
                $data['affected_batches'] ?? null
            );

            $totalAffectedQty = 0;

            // Create recall lines for each affected stock location
            foreach ($affectedStock as $stock) {
                RecallLine::create([
                    'company_id' => $companyId,
                    'recall_id' => $recall->id,
                    'warehouse_id' => $stock->warehouse_id,
                    'batch_id' => $stock->batch_id,
                    'affected_quantity' => $stock->quantity_on_hand,
                    'recovered_quantity' => 0,
                    'quarantined_quantity' => 0,
                    'status' => RecallLineStatus::Identified,
                ]);

                $totalAffectedQty += $stock->quantity_on_hand;
            }

            // Update recall with total affected quantity
            $recall->update(['total_affected_qty' => $totalAffectedQty]);

            return $recall->load(['item', 'lines.warehouse', 'lines.batch', 'initiatedBy']);
        });
    }

    /**
     * Quarantine a recall line
     */
    public function quarantineRecallLine(int $lineId): RecallLine
    {
        return DB::transaction(function () use ($lineId) {
            $line = RecallLine::findOrFail($lineId);

            if ($line->status !== RecallLineStatus::Identified) {
                throw new \RuntimeException('Only identified recall lines can be quarantined.');
            }

            // Create quarantine
            $quarantine = $this->quarantineService->createQuarantine([
                'item_id' => $line->recall->item_id,
                'batch_id' => $line->batch_id,
                'warehouse_id' => $line->warehouse_id,
                'quantity' => $line->affected_quantity,
                'reason_code' => 'RECALL',
                'reason_description' => "Recall #{$line->recall->recall_number}: {$line->recall->description}",
            ]);

            // Update recall line status
            $line->update([
                'status' => RecallLineStatus::Quarantined,
                'quarantined_quantity' => $line->affected_quantity,
            ]);

            return $line->fresh();
        });
    }

    /**
     * Mark recall line as recovered
     */
    public function recoverRecallLine(int $lineId, float $quantity): RecallLine
    {
        return DB::transaction(function () use ($lineId, $quantity) {
            $line = RecallLine::findOrFail($lineId);

            if ($line->status !== RecallLineStatus::Quarantined) {
                throw new \RuntimeException('Only quarantined recall lines can be marked as recovered.');
            }

            if ($quantity > $line->quarantined_quantity) {
                throw new \RuntimeException('Recovered quantity cannot exceed quarantined quantity.');
            }

            // Update recall line
            $line->update([
                'status' => RecallLineStatus::Recovered,
                'recovered_quantity' => $quantity,
            ]);

            // Update recall totals
            $recall = $line->recall;
            $totalRecovered = $recall->lines()->sum('recovered_quantity');
            $recall->update(['total_recovered_qty' => $totalRecovered]);

            return $line->fresh();
        });
    }

    /**
     * Complete the recall process
     */
    public function completeRecall(int $recallId): Recall
    {
        return DB::transaction(function () use ($recallId) {
            $recall = Recall::findOrFail($recallId);

            if ($recall->status === RecallStatus::Completed) {
                throw new \RuntimeException('Recall is already completed.');
            }

            // Verify all lines are recovered or disposed
            $pendingLines = $recall->lines()
                ->whereIn('status', [RecallLineStatus::Identified, RecallLineStatus::Quarantined])
                ->count();

            if ($pendingLines > 0) {
                throw new \RuntimeException("Cannot complete recall. {$pendingLines} line(s) are still pending.");
            }

            // Update recall status
            $recall->update([
                'status' => RecallStatus::Completed,
                'completion_date' => now()->toDateString(),
            ]);

            return $recall->fresh();
        });
    }

    /**
     * Identify affected stock based on item and batches
     */
    private function identifyAffectedStock(int $itemId, ?array $affectedBatches = null): \Illuminate\Support\Collection
    {
        $query = Stock::where('company_id', auth()->user()->company_id)
            ->where('item_id', $itemId)
            ->where('quantity_on_hand', '>', 0);

        // If specific batches are provided, filter by them
        if ($affectedBatches && count($affectedBatches) > 0) {
            $query->whereIn('batch_id', $affectedBatches);
        }

        return $query->get();
    }
}
