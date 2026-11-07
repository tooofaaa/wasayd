<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'approval_id', 'step_number', 'role_name', 'approver_id',
        'status', 'comments', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function approval(): BelongsTo
    {
        return $this->belongsTo(Approval::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
