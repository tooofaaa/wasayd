<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryTransaction extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'transaction_type' => TransactionType::class,
        'transaction_date' => 'datetime',
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'is_reversal' => 'boolean',
        'created_at' => 'datetime',
    ];

    use BelongsToCompany;

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function epc(): BelongsTo
    {
        return $this->belongsTo(Epc::class);
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of');
    }

    public function delete(): never
    {
        throw new \RuntimeException('Inventory transactions cannot be deleted.');
    }

    public function update(array $attributes = [], array $options = []): never
    {
        throw new \RuntimeException('Inventory transactions cannot be updated.');
    }
}
