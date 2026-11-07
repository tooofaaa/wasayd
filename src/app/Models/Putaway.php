<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Putaway extends Model
{
    use HasFactory, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'putaway_number', 'receiving_id', 'warehouse_id',
        'status', 'assigned_to', 'completed_at', 'created_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PutawayLine::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Putaway $putaway) {
            if (empty($putaway->putaway_number)) {
                $putaway->putaway_number = self::generatePutawayNumber($putaway->company_id);
            }
        });
    }

    private static function generatePutawayNumber(int $companyId): string
    {
        $date = now()->format('Ymd');
        $prefix = "PUT-{$date}-";

        $lastPutaway = self::where('company_id', $companyId)
            ->where('putaway_number', 'like', "{$prefix}%")
            ->orderByDesc('putaway_number')
            ->first();

        if ($lastPutaway) {
            $lastNumber = (int) substr($lastPutaway->putaway_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
