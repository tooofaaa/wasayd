<?php

namespace App\Services;

use App\Enums\ControlledAction;
use App\Enums\TransactionType;
use App\Models\ControlledMedicationLog;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class ControlledMedicationService
{
    public function __construct(
        private InventoryTransactionService $transactionService,
        private StockService $stockService
    ) {}

    /**
     * Dispense controlled medication (requires witness)
     */
    public function dispense(array $data): ControlledMedicationLog
    {
        $this->validateDualAuth($data['dispensed_by'], $data['witnessed_by']);

        return DB::transaction(function () use ($data) {
            $companyId = auth()->user()->company_id;
            $balanceBefore = $this->getBalance($data['item_id'], $data['warehouse_id'], $data['batch_id'] ?? null);

            // Create immutable log
            $log = ControlledMedicationLog::forceCreate([
                'company_id' => $companyId,
                'item_id' => $data['item_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'action' => ControlledAction::Dispense,
                'quantity' => $data['quantity'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $data['quantity'],
                'dispensed_by' => $data['dispensed_by'],
                'witnessed_by' => $data['witnessed_by'],
                'patient_id' => $data['patient_id'] ?? null,
                'prescription_number' => $data['prescription_number'] ?? null,
                'physician_name' => $data['physician_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'action_at' => now(),
            ]);

            // Create inventory transaction
            $this->transactionService->createTransaction([
                'company_id' => $companyId,
                'transaction_type' => TransactionType::Issue,
                'item_id' => $data['item_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'from_warehouse_id' => $data['warehouse_id'],
                'quantity' => -$data['quantity'],
                'reference_type' => ControlledMedicationLog::class,
                'reference_id' => $log->id,
                'notes' => "Controlled dispense - Patient: {$data['patient_id']} - Rx: {$data['prescription_number']}",
            ]);

            // Decrease stock
            $this->stockService->adjustStock(
                itemId: $data['item_id'],
                warehouseId: $data['warehouse_id'],
                binId: null,
                batchId: $data['batch_id'] ?? null,
                quantity: -$data['quantity'],
                field: 'quantity_on_hand'
            );

            $this->stockService->recalculateBalance($data['item_id'], $data['warehouse_id']);

            return $log->load(['item', 'batch', 'warehouse', 'dispensedBy', 'witnessedBy']);
        });
    }

    /**
     * Waste controlled medication (requires witness)
     */
    public function waste(array $data): ControlledMedicationLog
    {
        $this->validateDualAuth($data['dispensed_by'], $data['witnessed_by']);

        return DB::transaction(function () use ($data) {
            $companyId = auth()->user()->company_id;
            $balanceBefore = $this->getBalance($data['item_id'], $data['warehouse_id'], $data['batch_id'] ?? null);

            // Create immutable log
            $log = ControlledMedicationLog::forceCreate([
                'company_id' => $companyId,
                'item_id' => $data['item_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'action' => ControlledAction::Waste,
                'quantity' => $data['quantity'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $data['quantity'],
                'dispensed_by' => $data['dispensed_by'],
                'witnessed_by' => $data['witnessed_by'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'action_at' => now(),
            ]);

            // Create disposal transaction
            $this->transactionService->createTransaction([
                'company_id' => $companyId,
                'transaction_type' => TransactionType::Disposal,
                'item_id' => $data['item_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'from_warehouse_id' => $data['warehouse_id'],
                'quantity' => -$data['quantity'],
                'reference_type' => ControlledMedicationLog::class,
                'reference_id' => $log->id,
                'notes' => "Controlled waste - Reason: {$data['reason']}",
            ]);

            // Decrease stock
            $this->stockService->adjustStock(
                itemId: $data['item_id'],
                warehouseId: $data['warehouse_id'],
                binId: null,
                batchId: $data['batch_id'] ?? null,
                quantity: -$data['quantity'],
                field: 'quantity_on_hand'
            );

            $this->stockService->recalculateBalance($data['item_id'], $data['warehouse_id']);

            return $log->load(['item', 'batch', 'warehouse', 'dispensedBy', 'witnessedBy']);
        });
    }

    /**
     * Reconcile controlled medication inventory (requires witness)
     */
    public function reconcile(array $data): ControlledMedicationLog
    {
        $this->validateDualAuth($data['dispensed_by'], $data['witnessed_by']);

        return DB::transaction(function () use ($data) {
            $companyId = auth()->user()->company_id;
            $balanceBefore = $this->getBalance($data['item_id'], $data['warehouse_id'], $data['batch_id'] ?? null);
            $expectedBalance = $data['expected_balance'];
            $actualBalance = $data['actual_balance'];
            $variance = $actualBalance - $expectedBalance;

            // Create immutable log
            $log = ControlledMedicationLog::forceCreate([
                'company_id' => $companyId,
                'item_id' => $data['item_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'action' => ControlledAction::Reconcile,
                'quantity' => $variance,
                'balance_before' => $balanceBefore,
                'balance_after' => $actualBalance,
                'dispensed_by' => $data['dispensed_by'],
                'witnessed_by' => $data['witnessed_by'],
                'reason' => $data['reason'] ?? 'Reconciliation',
                'notes' => $data['notes'] ?? "Expected: {$expectedBalance}, Actual: {$actualBalance}, Variance: {$variance}",
                'action_at' => now(),
            ]);

            if ($variance != 0) {
                // Create adjustment transaction
                $this->transactionService->createTransaction([
                    'company_id' => $companyId,
                    'transaction_type' => TransactionType::Adjustment,
                    'item_id' => $data['item_id'],
                    'batch_id' => $data['batch_id'] ?? null,
                    'to_warehouse_id' => $data['warehouse_id'],
                    'quantity' => $variance,
                    'reference_type' => ControlledMedicationLog::class,
                    'reference_id' => $log->id,
                    'notes' => "Controlled reconciliation - Variance: {$variance}",
                ]);

                // Adjust stock
                $this->stockService->adjustStock(
                    itemId: $data['item_id'],
                    warehouseId: $data['warehouse_id'],
                    binId: null,
                    batchId: $data['batch_id'] ?? null,
                    quantity: $variance,
                    field: 'quantity_on_hand'
                );

                $this->stockService->recalculateBalance($data['item_id'], $data['warehouse_id']);
            }

            return $log->load(['item', 'batch', 'warehouse', 'dispensedBy', 'witnessedBy']);
        });
    }

    /**
     * Receive controlled medication
     */
    public function receive(array $data): ControlledMedicationLog
    {
        return DB::transaction(function () use ($data) {
            $companyId = auth()->user()->company_id;
            $balanceBefore = $this->getBalance($data['item_id'], $data['warehouse_id'], $data['batch_id'] ?? null);

            // Create immutable log
            $log = ControlledMedicationLog::forceCreate([
                'company_id' => $companyId,
                'item_id' => $data['item_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'action' => ControlledAction::Receive,
                'quantity' => $data['quantity'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $data['quantity'],
                'dispensed_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
                'action_at' => now(),
            ]);

            return $log->load(['item', 'batch', 'warehouse', 'dispensedBy']);
        });
    }

    /**
     * Get current balance for controlled medication
     */
    public function getBalance(int $itemId, int $warehouseId, ?int $batchId = null): int
    {
        $stock = Stock::where('company_id', auth()->user()->company_id)
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('batch_id', $batchId)
            ->first();

        return $stock ? (int) $stock->quantity_on_hand : 0;
    }

    /**
     * Validate dual authorization - dispensed_by and witnessed_by must be different users
     */
    public function validateDualAuth(int $dispensedBy, int $witnessedBy): void
    {
        if ($dispensedBy === $witnessedBy) {
            throw new \RuntimeException('Dual authorization required: Dispensed by and witnessed by must be different users.');
        }
    }
}
