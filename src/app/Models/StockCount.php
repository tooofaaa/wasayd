<?php

namespace App\Models;

use App\Enums\StockCountStatus;
use App\Enums\StockCountType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockCount extends Model
{
    use BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'count_number', 'warehouse_id', 'count_type', 'status',
        'planned_date', 'started_at', 'completed_at', 'zone_ids', 'category_ids',
        'total_items', 'total_lines', 'total_variance_qty', 'total_variance_value',
        'created_by', 'assigned_to', 'reviewed_by', 'notes',
    ];

    protected $casts = [
        'count_type' => StockCountType::class,
        'status' => StockCountStatus::class,
        'planned_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'zone_ids' => 'array',
        'category_ids' => 'array',
        'total_items' => 'integer',
        'total_lines' => 'integer',
        'total_variance_qty' => 'decimal:3',
        'total_variance_value' => 'decimal:4',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StockCountLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->count_number) {
                $model->count_number = static::generateCountNumber($model->company_id);
            }
            if (!$model->created_by) {
                $model->created_by = auth()->id();
            }
        });
    }

    public static function generateCountNumber(int $companyId): string
    {
        $date = now()->format('Ymd');
        $prefix = "SC-{$date}-";

        $lastCount = static::where('company_id', $companyId)
            ->where('count_number', 'like', "{$prefix}%")
            ->orderByDesc('count_number')
            ->first();

        if ($lastCount) {
            $lastNumber = (int) substr($lastCount->count_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
