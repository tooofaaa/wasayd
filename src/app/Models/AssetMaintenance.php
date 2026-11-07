<?php

namespace App\Models;

use App\Enums\MaintenanceStatus;
use App\Enums\MaintenanceType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    use BelongsToCompany, HasAuditTrail;

    protected $table = 'asset_maintenance';

    protected $fillable = [
        'company_id', 'asset_id', 'maintenance_type', 'status',
        'scheduled_date', 'started_date', 'completed_date', 'performed_by',
        'cost', 'findings', 'actions_taken', 'notes', 'next_due_date', 'created_by',
    ];

    protected $casts = [
        'maintenance_type' => MaintenanceType::class,
        'status' => MaintenanceStatus::class,
        'scheduled_date' => 'date',
        'started_date' => 'date',
        'completed_date' => 'date',
        'next_due_date' => 'date',
        'cost' => 'decimal:4',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
