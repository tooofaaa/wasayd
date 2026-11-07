<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\CompleteMaintenanceRequest;
use App\Http\Requests\Assets\ScheduleMaintenanceRequest;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Services\AssetService;
use Illuminate\Http\RedirectResponse;

class AssetMaintenanceController extends Controller
{
    public function __construct(
        protected AssetService $assetService
    ) {
    }

    public function store(ScheduleMaintenanceRequest $request, Asset $asset): RedirectResponse
    {
        $this->assetService->scheduleMaintenance($asset->id, $request->validated());

        return back()->with('success', __('Maintenance scheduled successfully.'));
    }

    public function update(ScheduleMaintenanceRequest $request, Asset $asset, AssetMaintenance $maintenance): RedirectResponse
    {
        $maintenance->update($request->validated());

        return back()->with('success', __('Maintenance updated successfully.'));
    }

    public function start(Asset $asset, AssetMaintenance $maintenance): RedirectResponse
    {
        $this->assetService->startMaintenance($maintenance->id);

        return back()->with('success', __('Maintenance started successfully.'));
    }

    public function complete(CompleteMaintenanceRequest $request, Asset $asset, AssetMaintenance $maintenance): RedirectResponse
    {
        $this->assetService->completeMaintenance($maintenance->id, $request->validated());

        return back()->with('success', __('Maintenance completed successfully.'));
    }
}
