<?php

namespace App\Models;

use App\Enums\InspectionResult;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetInspection extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'asset_id', 'inspection_date', 'inspector',
        'condition_rating', 'result', 'findings', 'recommendations',
        'next_inspection_date', 'created_by',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'next_inspection_date' => 'date',
        'result' => InspectionResult::class,
        'condition_rating' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function inspectorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
