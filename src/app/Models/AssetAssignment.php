<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\AssetCondition;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    use BelongsToCompany, HasAuditTrail;

    protected $fillable = [
        'company_id', 'asset_id', 'assigned_to_user_id', 'assigned_to_department',
        'assignment_type', 'assigned_at', 'expected_return_at', 'returned_at',
        'condition_at_assignment', 'condition_at_return', 'notes',
        'assigned_by', 'returned_to', 'created_by',
    ];

    protected $casts = [
        'assignment_type' => AssignmentType::class,
        'condition_at_assignment' => AssetCondition::class,
        'condition_at_return' => AssetCondition::class,
        'assigned_at' => 'datetime',
        'expected_return_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }
}
