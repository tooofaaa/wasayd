<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'warehouse_id', 'name', 'name_ar', 'code', 'type',
        'temperature_controlled', 'temperature_min', 'temperature_max', 'is_active',
    ];

    protected $casts = [
        'temperature_controlled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function aisles(): HasMany
    {
        return $this->hasMany(Aisle::class);
    }

    public function getFullPathAttribute(): string
    {
        return $this->warehouse->name . ' > ' . $this->name;
    }
}
