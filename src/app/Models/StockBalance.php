<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'item_id', 'warehouse_id', 'total_on_hand',
        'total_reserved', 'total_in_transit', 'weighted_avg_cost',
    ];

    // total_available is a MySQL GENERATED STORED column — never set manually

    protected $casts = [
        'total_on_hand' => 'decimal:3',
        'total_reserved' => 'decimal:3',
        'total_available' => 'decimal:3',
        'total_in_transit' => 'decimal:3',
        'weighted_avg_cost' => 'decimal:4',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
