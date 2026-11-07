<?php

namespace App\Models;

use App\Enums\RecallLineStatus;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecallLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'recall_id',
        'warehouse_id',
        'batch_id',
        'affected_quantity',
        'recovered_quantity',
        'quarantined_quantity',
        'status',
    ];

    protected $casts = [
        'affected_quantity' => 'decimal:3',
        'recovered_quantity' => 'decimal:3',
        'quarantined_quantity' => 'decimal:3',
        'status' => RecallLineStatus::class,
    ];

    public function recall(): BelongsTo
    {
        return $this->belongsTo(Recall::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
