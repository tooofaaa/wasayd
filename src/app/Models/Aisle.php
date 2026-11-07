<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aisle extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'zone_id', 'name', 'code', 'sequence', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function racks(): HasMany
    {
        return $this->hasMany(Rack::class);
    }
}
