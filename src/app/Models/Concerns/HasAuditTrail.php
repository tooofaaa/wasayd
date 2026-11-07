<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAuditTrail
{
    public static function bootHasAuditTrail(): void
    {
        static::created(fn ($model) => $model->logAudit('created'));
        static::updated(fn ($model) => $model->logAudit('updated'));
        static::deleted(fn ($model) => $model->logAudit('deleted'));
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected function logAudit(string $event): void
    {
        if (! auth()->check()) {
            return;
        }

        $changes = match ($event) {
            'created' => ['new' => $this->getAttributes()],
            'updated' => ['old' => $this->getOriginal(), 'new' => $this->getDirty()],
            'deleted' => ['old' => $this->getOriginal()],
            default => [],
        };

        AuditLog::forceCreate([
            'company_id' => $this->company_id ?? auth()->user()->company_id,
            'user_id' => auth()->id(),
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $changes['old'] ?? null,
            'new_values' => $changes['new'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }
}
