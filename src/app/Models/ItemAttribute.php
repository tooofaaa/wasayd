<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemAttribute extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'item_id', 'attribute_name', 'attribute_name_ar',
        'attribute_value', 'attribute_type', 'sort_order',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
