<?php

namespace App\Models;

use App\Enums\RfidEventType;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidReadEvent extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'reader_id', 'epc_id', 'epc_code', 'rssi',
        'antenna_port', 'read_count', 'first_read_at', 'last_read_at',
        'event_type', 'session_id', 'processed',
    ];

    protected $casts = [
        'event_type' => RfidEventType::class,
        'first_read_at' => 'datetime',
        'last_read_at' => 'datetime',
        'processed' => 'boolean',
    ];

    public function reader(): BelongsTo
    {
        return $this->belongsTo(RfidReader::class, 'reader_id');
    }

    public function epc(): BelongsTo
    {
        return $this->belongsTo(Epc::class);
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}
