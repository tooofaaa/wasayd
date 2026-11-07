<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bin extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'shelf_id', 'name', 'code', 'barcode',
        'max_weight_kg', 'max_volume_m3', 'is_occupied', 'is_active',
    ];

    protected $casts = [
        'is_occupied' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    public function getFullPathAttribute(): string
    {
        $shelf = $this->shelf;
        $rack = $shelf->rack;
        $aisle = $rack->aisle;
        $zone = $aisle->zone;
        $warehouse = $zone->warehouse;

        return implode(' > ', [
            $warehouse->code,
            $zone->code,
            $aisle->code,
            $rack->code,
            $shelf->code,
            $this->code,
        ]);
    }
}
