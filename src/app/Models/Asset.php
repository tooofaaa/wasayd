<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'asset_code', 'item_id', 'epc_id', 'serial_number',
        'name', 'name_ar', 'description', 'status', 'asset_category',
        'current_warehouse_id', 'current_zone_id', 'department',
        'purchase_date', 'purchase_price', 'supplier',
        'warranty_start_date', 'warranty_end_date', 'condition_rating',
        'expected_life_years', 'last_maintenance_date', 'next_maintenance_date',
        'last_calibration_date', 'next_calibration_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'status' => AssetStatus::class,
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:4',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'last_calibration_date' => 'date',
        'next_calibration_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function epc(): BelongsTo
    {
        return $this->belongsTo(Epc::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'current_warehouse_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(AssetInspection::class);
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(AssetWarranty::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isWarrantyActive(): bool
    {
        return $this->warranty_end_date && $this->warranty_end_date->isFuture();
    }

    public function isMaintenanceDue(): bool
    {
        return $this->next_maintenance_date && $this->next_maintenance_date->isPast();
    }
}
