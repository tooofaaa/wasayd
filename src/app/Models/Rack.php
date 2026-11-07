<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rack extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'aisle_id', 'name', 'code', 'levels', 'max_weight_kg', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function aisle(): BelongsTo
    {
        return $this->belongsTo(Aisle::class);
    }

    public function shelves(): HasMany
    {
        return $this->hasMany(Shelf::class);
    }
}
