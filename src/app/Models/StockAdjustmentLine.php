<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'stock_adjustment_id', 'item_id', 'batch_id', 'bin_id',
        'warehouse_id', 'system_quantity', 'adjusted_quantity', 'variance', 'reason',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:3',
        'adjusted_quantity' => 'decimal:3',
        'variance' => 'decimal:3',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->adjusted_quantity !== null && $model->system_quantity !== null) {
                $model->variance = $model->adjusted_quantity - $model->system_quantity;
            }
        });
    }
}
