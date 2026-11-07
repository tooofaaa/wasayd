<?php

namespace App\Models;

use App\Enums\ReceivingStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receiving extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'receiving_number', 'warehouse_id', 'supplier_name',
        'supplier_reference', 'purchase_order_number', 'receiving_date', 'status',
        'receiving_method', 'total_expected_qty', 'total_received_qty',
        'total_rejected_qty', 'notes', 'completed_at', 'created_by', 'completed_by',
    ];

    protected $casts = [
        'status' => ReceivingStatus::class,
        'receiving_date' => 'date',
        'completed_at' => 'datetime',
        'total_expected_qty' => 'decimal:3',
        'total_received_qty' => 'decimal:3',
        'total_rejected_qty' => 'decimal:3',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ReceivingLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Receiving $receiving) {
            if (empty($receiving->receiving_number)) {
                $receiving->receiving_number = self::generateReceivingNumber($receiving->company_id);
            }
        });
    }

    private static function generateReceivingNumber(int $companyId): string
    {
        $date = now()->format('Ymd');
        $prefix = "RCV-{$date}-";

        $lastReceiving = self::where('company_id', $companyId)
            ->where('receiving_number', 'like', "{$prefix}%")
            ->orderByDesc('receiving_number')
            ->first();

        if ($lastReceiving) {
            $lastNumber = (int) substr($lastReceiving->receiving_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
