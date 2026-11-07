<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\TransferStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\AssetTransfer;
use Illuminate\Support\Facades\DB;

class AssetService
{
    /**
     * Register a new asset with auto-generated asset code
     */
    public function register(array $data): Asset
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        $data['status'] = $data['status'] ?? AssetStatus::Available;

        // Auto-generate asset code if not provided
        if (empty($data['asset_code'])) {
            $data['asset_code'] = $this->generateAssetCode();
        }

        return Asset::create($data);
    }

    /**
     * Assign asset to user/department/location
     */
    public function assign(int $assetId, array $data): AssetAssignment
    {
        return DB::transaction(function () use ($assetId, $data) {
            $asset = Asset::findOrFail($assetId);

            $data['company_id'] = auth()->user()->company_id;
            $data['asset_id'] = $assetId;
            $data['assigned_by'] = auth()->id();
            $data['assigned_at'] = $data['assigned_at'] ?? now();

            $assignment = AssetAssignment::create($data);

            // Update asset status to in_use
            $asset->update(['status' => AssetStatus::InUse]);

            return $assignment;
        });
    }

    /**
     * Return an asset from assignment
     */
    public function returnAsset(int $assignmentId, array $data): AssetAssignment
    {
        return DB::transaction(function () use ($assignmentId, $data) {
            $assignment = AssetAssignment::findOrFail($assignmentId);

            $assignment->update([
                'returned_at' => $data['returned_at'] ?? now(),
                'condition_at_return' => $data['condition_at_return'],
                'returned_to' => auth()->id(),
                'notes' => $data['notes'] ?? $assignment->notes,
            ]);

            // Update asset status back to available
            $assignment->asset->update(['status' => AssetStatus::Available]);

            return $assignment->fresh();
        });
    }

    /**
     * Create transfer request (pending approval)
     */
    public function transfer(int $assetId, array $data): AssetTransfer
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['asset_id'] = $assetId;
        $data['created_by'] = auth()->id();
        $data['status'] = TransferStatus::Pending;
        $data['transfer_date'] = $data['transfer_date'] ?? now();

        return AssetTransfer::create($data);
    }

    /**
     * Approve transfer and start in-transit
     */
    public function approveTransfer(int $transferId): AssetTransfer
    {
        return DB::transaction(function () use ($transferId) {
            $transfer = AssetTransfer::findOrFail($transferId);

            $transfer->update([
                'status' => TransferStatus::InTransit,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return $transfer->fresh();
        });
    }

    /**
     * Complete transfer and update asset warehouse
     */
    public function completeTransfer(int $transferId): AssetTransfer
    {
        return DB::transaction(function () use ($transferId) {
            $transfer = AssetTransfer::findOrFail($transferId);

            $transfer->update([
                'status' => TransferStatus::Completed,
                'completed_at' => now(),
            ]);

            // Update asset's current warehouse
            $transfer->asset->update([
                'current_warehouse_id' => $transfer->to_warehouse_id,
            ]);

            return $transfer->fresh();
        });
    }

    /**
     * Schedule maintenance for an asset
     */
    public function scheduleMaintenance(int $assetId, array $data): AssetMaintenance
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['asset_id'] = $assetId;
        $data['created_by'] = auth()->id();
        $data['status'] = MaintenanceStatus::Scheduled;

        return AssetMaintenance::create($data);
    }

    /**
     * Start maintenance
     */
    public function startMaintenance(int $maintenanceId): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenanceId) {
            $maintenance = AssetMaintenance::findOrFail($maintenanceId);

            $maintenance->update([
                'status' => MaintenanceStatus::InProgress,
                'started_date' => now(),
            ]);

            // Update asset status to maintenance
            $maintenance->asset->update(['status' => AssetStatus::Maintenance]);

            return $maintenance->fresh();
        });
    }

    /**
     * Complete maintenance and set next due date
     */
    public function completeMaintenance(int $maintenanceId, array $data): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenanceId, $data) {
            $maintenance = AssetMaintenance::findOrFail($maintenanceId);

            $maintenance->update([
                'status' => MaintenanceStatus::Completed,
                'completed_date' => $data['completed_date'] ?? now(),
                'findings' => $data['findings'] ?? null,
                'actions_taken' => $data['actions_taken'] ?? null,
                'cost' => $data['cost'] ?? null,
                'next_due_date' => $data['next_due_date'] ?? null,
            ]);

            // Update asset
            $asset = $maintenance->asset;
            $asset->update([
                'status' => AssetStatus::Available,
                'last_maintenance_date' => $maintenance->completed_date,
                'next_maintenance_date' => $maintenance->next_due_date,
            ]);

            return $maintenance->fresh();
        });
    }

    /**
     * Record inspection for an asset
     */
    public function recordInspection(int $assetId, array $data): \App\Models\AssetInspection
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['asset_id'] = $assetId;
        $data['created_by'] = auth()->id();
        $data['inspection_date'] = $data['inspection_date'] ?? now();

        return \App\Models\AssetInspection::create($data);
    }

    /**
     * Dispose asset (requires approval)
     */
    public function dispose(int $assetId, string $reason): Asset
    {
        $asset = Asset::findOrFail($assetId);

        $asset->update([
            'status' => AssetStatus::Disposed,
            'notes' => ($asset->notes ? $asset->notes . "\n\n" : '') . "Disposed: {$reason}",
        ]);

        return $asset;
    }

    /**
     * Generate unique asset code
     */
    protected function generateAssetCode(): string
    {
        $date = now()->format('Ymd');
        $prefix = "AST-{$date}-";

        // Find the last asset code for today
        $lastAsset = Asset::where('asset_code', 'like', $prefix . '%')
            ->orderBy('asset_code', 'desc')
            ->first();

        if ($lastAsset) {
            // Extract the number and increment
            $lastNumber = (int) substr($lastAsset->asset_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
