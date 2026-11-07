<?php

namespace App\Models;

use App\Enums\RecallClass;
use App\Enums\RecallStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recall extends Model
{
    use HasFactory, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id',
        'item_id',
        'recall_number',
        'recall_class',
        'manufacturer_recall_id',
        'status',
        'description',
        'instructions',
        'affected_batches',
        'total_affected_qty',
        'total_recovered_qty',
        'initiated_by',
        'initiated_date',
        'completion_date',
        'metadata',
    ];

    protected $casts = [
        'recall_class' => RecallClass::class,
        'status' => RecallStatus::class,
        'affected_batches' => 'array',
        'total_affected_qty' => 'integer',
        'total_recovered_qty' => 'integer',
        'initiated_date' => 'date',
        'completion_date' => 'date',
        'metadata' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(RecallLine::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Recall $recall) {
            if (empty($recall->recall_number)) {
                $recall->recall_number = self::generateRecallNumber($recall->company_id);
            }
        });
    }

    private static function generateRecallNumber(int $companyId): string
    {
        $date = now()->format('Ymd');
        $prefix = "RCL-{$date}-";

        $lastRecall = self::where('company_id', $companyId)
            ->where('recall_number', 'like', "{$prefix}%")
            ->orderByDesc('recall_number')
            ->first();

        if ($lastRecall) {
            $lastNumber = (int) substr($lastRecall->recall_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getRecoveryPercentageAttribute(): float
    {
        if ($this->total_affected_qty == 0) {
            return 0;
        }

        return round(($this->total_recovered_qty / $this->total_affected_qty) * 100, 2);
    }
}
