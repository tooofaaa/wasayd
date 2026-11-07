<?php

namespace App\Models;

use App\Enums\QuarantineResolution;
use App\Enums\QuarantineStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quarantine extends Model
{
    use HasFactory, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id',
        'item_id',
        'batch_id',
        'warehouse_id',
        'bin_id',
        'quarantine_number',
        'quantity',
        'reason_code',
        'reason_description',
        'status',
        'resolution',
        'resolution_notes',
        'quarantined_by',
        'resolved_by',
        'quarantined_at',
        'resolved_at',
    ];

    protected $casts = [
        'status' => QuarantineStatus::class,
        'resolution' => QuarantineResolution::class,
        'quantity' => 'integer',
        'quarantined_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    public function quarantinedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quarantined_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Quarantine $quarantine) {
            if (empty($quarantine->quarantine_number)) {
                $quarantine->quarantine_number = self::generateQuarantineNumber($quarantine->company_id);
            }
        });
    }

    private static function generateQuarantineNumber(int $companyId): string
    {
        $date = now()->format('Ymd');
        $prefix = "QRN-{$date}-";

        $lastQuarantine = self::where('company_id', $companyId)
            ->where('quarantine_number', 'like', "{$prefix}%")
            ->orderByDesc('quarantine_number')
            ->first();

        if ($lastQuarantine) {
            $lastNumber = (int) substr($lastQuarantine->quarantine_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
