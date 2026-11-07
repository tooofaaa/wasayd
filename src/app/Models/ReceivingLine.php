<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivingLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'receiving_id', 'item_id', 'batch_id',
        'expected_quantity', 'received_quantity', 'rejected_quantity',
        'uom_id', 'unit_cost', 'batch_number', 'expiry_date',
        'serial_number', 'rejection_reason', 'line_number',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'expiry_date' => 'date',
    ];

    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class, 'uom_id');
    }
}
