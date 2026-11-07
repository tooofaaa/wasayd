<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PutawayLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'putaway_id', 'receiving_line_id', 'item_id', 'batch_id',
        'quantity', 'from_zone_id', 'to_zone_id', 'to_bin_id', 'status', 'placed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'placed_at' => 'datetime',
    ];

    public function putaway(): BelongsTo
    {
        return $this->belongsTo(Putaway::class);
    }

    public function receivingLine(): BelongsTo
    {
        return $this->belongsTo(ReceivingLine::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function fromZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'from_zone_id');
    }

    public function toZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'to_zone_id');
    }

    public function toBin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'to_bin_id');
    }
}
