<?php

namespace App\Services;

use App\Enums\QuarantineResolution;
use App\Enums\QuarantineStatus;
use App\Enums\TransactionType;
use App\Models\Quarantine;
use Illuminate\Support\Facades\DB;

class QuarantineService
{
    public function __construct(
        private InventoryTransactionService $transactionService,
        private StockService $stockService
    ) {}

    /**
     * Create a quarantine and lock affected stock
     */
    public function createQuarantine(array $data): Quarantine
    {
        return DB::transaction(function () use ($data) {
            $companyId = auth()->user()->company_id;

            // Create quarantine record
            $quarantine = Quarantine::create([
                'company_id' => $companyId,
                'item_id' => $data['item_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'bin_id' => $data['bin_id'] ?? null,
                'quantity' => $data['quantity'],
                'reason_code' => $data['reason_code'],
                'reason_description' => $data['reason_description'] ?? null,
                'status' => QuarantineStatus::Active,
                'quarantined_by' => auth()->id(),
                'quarantined_at' => now(),
            ]);

            // Create inventory transaction (quarantine decreases available stock)
            $this->transactionService->createTransaction([
                'company_id' => $companyId,
                'transaction_type' => TransactionType::Quarantine,
                'item_id' => $data['item_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'from_warehouse_id' => $data['warehouse_id'],
                'quantity' => -$data['quantity'],
                'reference_type' => Quarantine::class,
                'reference_id' => $quarantine->id,
                'notes' => "Quarantine: {$data['reason_code']}",
            ]);

            // Adjust stock (decrease quantity_on_hand, increase reserved/quarantined)
            $this->stockService->adjustStock(
                itemId: $data['item_id'],
                warehouseId: $data['warehouse_id'],
                binId: $data['bin_id'] ?? null,
                batchId: $data['batch_id'] ?? null,
                quantity: -$data['quantity'],
                field: 'quantity_on_hand'
            );

            // Recalculate balance
            $this->stockService->recalculateBalance($data['item_id'], $data['warehouse_id']);

            return $quarantine->load(['item', 'batch', 'warehouse', 'bin', 'quarantinedBy']);
        });
    }

    /**
     * Release quarantine and return stock to available
     */
    public function releaseQuarantine(int $quarantineId, ?string $notes = null): Quarantine
    {
        return DB::transaction(function () use ($quarantineId, $notes) {
            $quarantine = Quarantine::findOrFail($quarantineId);

            if ($quarantine->status !== QuarantineStatus::Active) {
                throw new \RuntimeException('Only active quarantines can be released.');
            }

            // Update quarantine status
            $quarantine->update([
                'status' => QuarantineStatus::Released,
                'resolution' => QuarantineResolution::Released,
                'resolution_notes' => $notes,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            // Create reversal transaction (unquarantine increases available stock)
            $this->transactionService->createTransaction([
                'company_id' => $quarantine->company_id,
                'transaction_type' => TransactionType::Unquarantine,
                'item_id' => $quarantine->item_id,
                'batch_id' => $quarantine->batch_id,
                'to_warehouse_id' => $quarantine->warehouse_id,
                'quantity' => $quarantine->quantity,
                'reference_type' => Quarantine::class,
                'reference_id' => $quarantine->id,
                'notes' => "Quarantine released: {$notes}",
            ]);

            // Adjust stock (increase quantity_on_hand back)
            $this->stockService->adjustStock(
                itemId: $quarantine->item_id,
                warehouseId: $quarantine->warehouse_id,
                binId: $quarantine->bin_id,
                batchId: $quarantine->batch_id,
                quantity: $quarantine->quantity,
                field: 'quantity_on_hand'
            );

            // Recalculate balance
            $this->stockService->recalculateBalance($quarantine->item_id, $quarantine->warehouse_id);

            return $quarantine->fresh();
        });
    }

    /**
     * Dispose quarantined stock (permanent removal)
     */
    public function disposeQuarantine(int $quarantineId, ?string $notes = null): Quarantine
    {
        return DB::transaction(function () use ($quarantineId, $notes) {
            $quarantine = Quarantine::findOrFail($quarantineId);

            if ($quarantine->status !== QuarantineStatus::Active) {
                throw new \RuntimeException('Only active quarantines can be disposed.');
            }

            // Update quarantine status
            $quarantine->update([
                'status' => QuarantineStatus::Disposed,
                'resolution' => QuarantineResolution::Disposed,
                'resolution_notes' => $notes,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            // Create disposal transaction (permanent removal)
            $this->transactionService->createTransaction([
                'company_id' => $quarantine->company_id,
                'transaction_type' => TransactionType::Disposal,
                'item_id' => $quarantine->item_id,
                'batch_id' => $quarantine->batch_id,
                'from_warehouse_id' => $quarantine->warehouse_id,
                'quantity' => -$quarantine->quantity,
                'reference_type' => Quarantine::class,
                'reference_id' => $quarantine->id,
                'notes' => "Quarantined stock disposed: {$notes}",
            ]);

            // Stock was already decreased during quarantine, so no additional adjustment needed
            // Just recalculate balance
            $this->stockService->recalculateBalance($quarantine->item_id, $quarantine->warehouse_id);

            return $quarantine->fresh();
        });
    }
}
