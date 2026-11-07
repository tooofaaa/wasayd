<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryTransactionService
{
    /**
     * Create an immutable inventory transaction ledger entry
     */
    public function createTransaction(array $data): InventoryTransaction
    {
        $data['transaction_number'] = $this->generateTransactionNumber($data['company_id']);
        $data['created_by'] = $data['created_by'] ?? auth()->id();
        $data['transaction_date'] = $data['transaction_date'] ?? now();
        $data['created_at'] = now();

        return InventoryTransaction::create($data);
    }

    /**
     * Create a reversal transaction (opposite quantity)
     */
    public function createReversal(InventoryTransaction $original, string $reason): InventoryTransaction
    {
        return $this->createTransaction([
            'company_id' => $original->company_id,
            'transaction_type' => $original->transaction_type,
            'item_id' => $original->item_id,
            'batch_id' => $original->batch_id,
            'epc_id' => $original->epc_id,
            'from_warehouse_id' => $original->from_warehouse_id,
            'to_warehouse_id' => $original->to_warehouse_id,
            'quantity' => -$original->quantity,
            'unit_cost' => $original->unit_cost,
            'total_cost' => -$original->total_cost,
            'is_reversal' => true,
            'reversal_of' => $original->id,
            'reason' => "Reversal: {$reason}",
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Generate unique transaction number per company per day
     */
    private function generateTransactionNumber(int $companyId): string
    {
        $date = now()->format('Ymd');
        $prefix = "TXN-{$date}-";

        $lastTransaction = InventoryTransaction::where('company_id', $companyId)
            ->where('transaction_number', 'like', "{$prefix}%")
            ->orderByDesc('transaction_number')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
