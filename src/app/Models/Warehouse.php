<?php

namespace App\Models;

use App\Enums\WarehouseType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'name_ar', 'code', 'type',
        'address', 'total_capacity', 'temperature_min', 'temperature_max',
        'humidity_min', 'humidity_max', 'is_active',
    ];

    protected $casts = [
        'type' => WarehouseType::class,
        'total_capacity' => 'decimal:2',
        'temperature_min' => 'decimal:2',
        'temperature_max' => 'decimal:2',
        'humidity_min' => 'decimal:2',
        'humidity_max' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }
}
