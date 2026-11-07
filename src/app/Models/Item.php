<?php

namespace App\Models;

use App\Enums\ItemType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'category_id', 'subcategory_id', 'base_uom_id', 'sku', 'name', 'name_ar',
        'description', 'item_type', 'manufacturer', 'brand', 'model_number', 'unit_cost',
        'unit_price', 'reorder_point', 'reorder_qty', 'min_stock', 'max_stock', 'lead_time_days',
        'requires_batch', 'requires_expiry', 'requires_serial', 'requires_rfid', 'is_controlled',
        'is_hazardous', 'storage_conditions', 'weight_kg', 'length_cm', 'width_cm', 'height_cm',
        'image_url', 'metadata', 'is_active',
    ];

    protected $casts = [
        'item_type' => ItemType::class,
        'requires_batch' => 'boolean',
        'requires_expiry' => 'boolean',
        'requires_serial' => 'boolean',
        'requires_rfid' => 'boolean',
        'is_controlled' => 'boolean',
        'is_hazardous' => 'boolean',
        'is_active' => 'boolean',
        'storage_conditions' => 'array',
        'metadata' => 'array',
        'unit_cost' => 'decimal:4',
        'unit_price' => 'decimal:4',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(ItemSubcategory::class, 'subcategory_id');
    }

    public function baseUom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class, 'base_uom_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ItemBarcode::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ItemAttribute::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function epcs(): HasMany
    {
        return $this->hasMany(Epc::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, ItemType $type): Builder
    {
        return $query->where('item_type', $type);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereHas('stocks', function ($q) {
            $q->whereColumn('quantity_available', '<', 'items.min_stock');
        });
    }
}
