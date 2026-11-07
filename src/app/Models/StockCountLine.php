<?php

namespace App\Models;

use App\Enums\CountMethod;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'stock_count_id', 'item_id', 'batch_id', 'bin_id', 'epc_id',
        'system_quantity', 'counted_quantity', 'variance', 'variance_percentage',
        'count_method', 'counted_at', 'counted_by', 'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:3',
        'counted_quantity' => 'decimal:3',
        'variance' => 'decimal:3',
        'variance_percentage' => 'decimal:2',
        'count_method' => CountMethod::class,
        'counted_at' => 'datetime',
    ];

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
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

    public function epc(): BelongsTo
    {
        return $this->belongsTo(Epc::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->counted_quantity !== null && $model->system_quantity !== null) {
                $model->variance = $model->counted_quantity - $model->system_quantity;

                if ($model->system_quantity != 0) {
                    $model->variance_percentage = ($model->variance / $model->system_quantity) * 100;
                } else {
                    $model->variance_percentage = 0;
                }
            }
        });
    }
}
