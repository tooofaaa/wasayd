<?php

namespace App\Models;

use App\Enums\AdjustmentStatus;
use App\Enums\AdjustmentType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class StockAdjustment extends Model
{
    use BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'adjustment_number', 'stock_count_id', 'adjustment_type',
        'status', 'total_adjustment_qty', 'total_adjustment_value',
        'reason', 'requested_by', 'approved_by', 'approved_at', 'applied_at',
    ];

    protected $casts = [
        'adjustment_type' => AdjustmentType::class,
        'status' => AdjustmentStatus::class,
        'total_adjustment_qty' => 'decimal:3',
        'total_adjustment_value' => 'decimal:4',
        'approved_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StockAdjustmentLine::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->adjustment_number) {
                $model->adjustment_number = static::generateAdjustmentNumber($model->company_id);
            }
            if (!$model->requested_by) {
                $model->requested_by = auth()->id();
            }
        });
    }

    public static function generateAdjustmentNumber(int $companyId): string
    {
        $date = now()->format('Ymd');
        $prefix = "ADJ-{$date}-";

        $lastAdjustment = static::where('company_id', $companyId)
            ->where('adjustment_number', 'like', "{$prefix}%")
            ->orderByDesc('adjustment_number')
            ->first();

        if ($lastAdjustment) {
            $lastNumber = (int) substr($lastAdjustment->adjustment_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
