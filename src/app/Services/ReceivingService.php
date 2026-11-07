<?php

namespace App\Services;

use App\Enums\ReceivingStatus;
use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Receiving;
use App\Models\ReceivingLine;
use Illuminate\Support\Facades\DB;

class ReceivingService
{
    public function __construct(
        private InventoryTransactionService $transactionService,
        private StockService $stockService
    ) {}

    /**
     * Create a new receiving record
     */
    public function createReceiving(array $data): Receiving
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        $data['status'] = ReceivingStatus::Draft;
        $data['receiving_date'] = $data['receiving_date'] ?? now();

        $receiving = Receiving::create($data);

        return $receiving;
    }

    /**
     * Add a line to a receiving
     */
    public function addLine(Receiving $receiving, array $lineData): ReceivingLine
    {
        if (!in_array($receiving->status, [ReceivingStatus::Draft, ReceivingStatus::InProgress])) {
            throw new \RuntimeException('Cannot add lines to a receiving that is not in draft or in progress status.');
        }

        $lineData['company_id'] = auth()->user()->company_id;
        $lineData['receiving_id'] = $receiving->id;
        $lineData['line_number'] = $receiving->lines()->max('line_number') + 1;

        $line = ReceivingLine::create($lineData);

        // Update total expected quantity
        $receiving->increment('total_expected_qty', $lineData['expected_quantity']);

        return $line;
    }

    /**
     * Receive a line (record actual received quantity)
     */
    public function receiveLine(ReceivingLine $line, array $data): ReceivingLine
    {
        return DB::transaction(function () use ($line, $data) {
            $receiving = $line->receiving;

            if ($receiving->status === ReceivingStatus::Completed) {
                throw new \RuntimeException('Cannot receive lines for a completed receiving.');
            }

            // Update line with received quantity
            $line->update([
                'received_quantity' => $data['received_quantity'],
                'batch_number' => $data['batch_number'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
            ]);

            // Create batch if item is batch controlled
            $item = $line->item;
            $batchId = null;

            if ($item->requires_batch && !empty($data['batch_number'])) {
                $batch = Batch::firstOrCreate(
                    [
                        'company_id' => auth()->user()->company_id,
                        'item_id' => $item->id,
                        'batch_number' => $data['batch_number'],
                    ],
                    [
                        'expiry_date' => $data['expiry_date'] ?? null,
                        'received_date' => now(),
                        'status' => 'active',
                    ]
                );
                $batchId = $batch->id;
                $line->update(['batch_id' => $batchId]);
            }

            // Create inventory transaction
            $this->transactionService->createTransaction([
                'company_id' => auth()->user()->company_id,
                'transaction_type' => TransactionType::Receiving,
                'item_id' => $line->item_id,
                'batch_id' => $batchId,
                'to_warehouse_id' => $receiving->warehouse_id,
                'quantity' => $data['received_quantity'],
                'unit_cost' => $line->unit_cost ?? 0,
                'total_cost' => ($line->unit_cost ?? 0) * $data['received_quantity'],
                'reference_type' => ReceivingLine::class,
                'reference_id' => $line->id,
                'reason' => "Received from {$receiving->supplier_name}",
            ]);

            // Adjust stock (increase quantity_on_hand)
            $this->stockService->adjustStock(
                itemId: $line->item_id,
                warehouseId: $receiving->warehouse_id,
                binId: null, // Will be set during putaway
                batchId: $batchId,
                quantity: $data['received_quantity'],
                field: 'quantity_on_hand'
            );

            // Update receiving status to in_progress if still draft
            if ($receiving->status === ReceivingStatus::Draft) {
                $receiving->update(['status' => ReceivingStatus::InProgress]);
            }

            // Update total received quantity
            $receiving->increment('total_received_qty', $data['received_quantity']);

            return $line->fresh();
        });
    }

    /**
     * Complete the receiving
     */
    public function completeReceiving(Receiving $receiving): Receiving
    {
        return DB::transaction(function () use ($receiving) {
            // Validate all lines have been received
            $unreceivedLines = $receiving->lines()
                ->whereNull('received_quantity')
                ->orWhere('received_quantity', 0)
                ->count();

            if ($unreceivedLines > 0) {
                throw new \RuntimeException("Cannot complete receiving. {$unreceivedLines} lines have not been received.");
            }

            // Update receiving status
            $receiving->update([
                'status' => ReceivingStatus::Completed,
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);

            // Recalculate stock balances for all affected items
            foreach ($receiving->lines as $line) {
                $this->stockService->recalculateBalance(
                    itemId: $line->item_id,
                    warehouseId: $receiving->warehouse_id
                );
            }

            return $receiving->fresh();
        });
    }
}
