<?php

namespace App\Models;

use App\Enums\ControlledAction;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlledMedicationLog extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'item_id',
        'batch_id',
        'warehouse_id',
        'action',
        'quantity',
        'balance_before',
        'balance_after',
        'dispensed_by',
        'witnessed_by',
        'patient_id',
        'prescription_number',
        'physician_name',
        'reason',
        'notes',
        'action_at',
    ];

    protected $casts = [
        'action' => ControlledAction::class,
        'quantity' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'action_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function witnessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witnessed_by');
    }

    /**
     * Override delete to prevent deletion - this table is IMMUTABLE
     */
    public function delete()
    {
        throw new \RuntimeException('Controlled medication logs cannot be deleted. They are immutable audit records.');
    }

    /**
     * Override update to prevent updates - this table is IMMUTABLE
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new \RuntimeException('Controlled medication logs cannot be updated. They are immutable audit records.');
    }

    /**
     * Override save to prevent updates after creation - only allow creation
     */
    public function save(array $options = [])
    {
        if ($this->exists) {
            throw new \RuntimeException('Controlled medication logs cannot be modified. They are immutable audit records.');
        }

        return parent::save($options);
    }
}
