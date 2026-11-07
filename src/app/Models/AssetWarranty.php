<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetWarranty extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'asset_id', 'warranty_type', 'provider',
        'start_date', 'end_date', 'terms', 'coverage',
        'contact_name', 'contact_phone', 'contact_email', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function isActive(): bool
    {
        return $this->end_date && $this->end_date->isFuture();
    }

    public function isExpiring(int $daysAhead = 30): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return $this->end_date->isFuture() &&
               $this->end_date->diffInDays(now()) <= $daysAhead;
    }
}
