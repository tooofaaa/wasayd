<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelf extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'rack_id', 'name', 'code', 'level', 'max_weight_kg', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class);
    }

    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class);
    }
}
