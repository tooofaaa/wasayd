<?php

namespace App\Models;

use App\Enums\RfidReaderStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfidReader extends Model
{
    use HasFactory, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'name', 'reader_id', 'model', 'manufacturer',
        'ip_address', 'port', 'warehouse_id', 'zone_id', 'status',
        'last_heartbeat_at', 'firmware_version', 'configuration',
    ];

    protected $casts = [
        'status' => RfidReaderStatus::class,
        'configuration' => 'array',
        'last_heartbeat_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function readEvents(): HasMany
    {
        return $this->hasMany(RfidReadEvent::class, 'reader_id');
    }

    public function encodingLogs(): HasMany
    {
        return $this->hasMany(RfidEncodingLog::class, 'reader_id');
    }

    public function isOnline(): bool
    {
        return $this->status === RfidReaderStatus::Online
            && $this->last_heartbeat_at?->gt(now()->subMinutes(5));
    }
}
