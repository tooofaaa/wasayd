<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetWarranty;
use Illuminate\Support\Collection;

class AssetAlertService
{
    /**
     * Get assets needing maintenance
     */
    public function getMaintenanceDue(int $daysAhead = 7): Collection
    {
        $cutoffDate = now()->addDays($daysAhead);

        return Asset::query()
            ->whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<=', $cutoffDate)
            ->with(['item', 'warehouse'])
            ->get();
    }

    /**
     * Get warranties expiring soon
     */
    public function getWarrantyExpiring(int $daysAhead = 30): Collection
    {
        $cutoffDate = now()->addDays($daysAhead);

        return AssetWarranty::query()
            ->where('end_date', '>', now())
            ->where('end_date', '<=', $cutoffDate)
            ->with(['asset.item', 'asset.warehouse'])
            ->get();
    }

    /**
     * Get assets with overdue inspections
     */
    public function getOverdueInspections(): Collection
    {
        return Asset::query()
            ->whereHas('inspections', function ($query) {
                $query->where('next_inspection_date', '<', now());
            })
            ->with(['item', 'warehouse', 'inspections' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get();
    }

    /**
     * Get all alerts summary
     */
    public function getAlertsSummary(): array
    {
        return [
            'maintenance_due' => $this->getMaintenanceDue()->count(),
            'warranty_expiring' => $this->getWarrantyExpiring()->count(),
            'overdue_inspections' => $this->getOverdueInspections()->count(),
        ];
    }
}
