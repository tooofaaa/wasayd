<?php

namespace App\Models;

use App\Enums\EpcStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Epc extends Model
{
    use HasFactory, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'item_id', 'batch_id', 'epc', 'tid', 'sgtin',
        'status', 'current_location_id', 'warehouse_id', 'encoded_at',
        'encoded_by', 'last_seen_at', 'last_seen_reader', 'metadata',
    ];

    protected $casts = [
        'status' => EpcStatus::class,
        'metadata' => 'array',
        'encoded_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function canTransitionTo(EpcStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }
}
