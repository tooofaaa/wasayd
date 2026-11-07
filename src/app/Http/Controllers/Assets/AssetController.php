<?php

namespace App\Http\Controllers\Assets;

use App\Enums\AssetStatus;
use App\Enums\ItemType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\AssignAssetRequest;
use App\Http\Requests\Assets\ReturnAssetRequest;
use App\Http\Requests\Assets\StoreAssetInspectionRequest;
use App\Http\Requests\Assets\StoreAssetRequest;
use App\Http\Requests\Assets\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\Item;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AssetService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    public function __construct(
        protected AssetService $assetService
    ) {
    }

    public function index(): Response
    {
        $search = request('search');
        $status = request('status');
        $category = request('category');
        $warehouseId = request('warehouse_id');

        $assets = Asset::query()
            ->with(['item:id,name,name_ar,sku', 'warehouse:id,name,name_ar', 'epc:id,epc'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('asset_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->when($category, fn($query, $category) => $query->where('category', $category))
            ->when($warehouseId, fn($query, $warehouseId) => $query->where('current_warehouse_id', $warehouseId))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();

        $statuses = collect(AssetStatus::cases())->map(fn($status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ]);

        return Inertia::render('Assets/Index', [
            'assets' => $assets,
            'warehouses' => $warehouses,
            'statuses' => $statuses,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category' => $category,
                'warehouse_id' => $warehouseId,
            ],
        ]);
    }

    public function create(): Response
    {
        $items = Item::query()
            ->whereIn('item_type', [ItemType::MedicalDevice->value, ItemType::MedicalEquipment->value])
            ->select('id', 'name', 'name_ar', 'sku', 'item_type')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();

        return Inertia::render('Assets/Create', [
            'items' => $items,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $this->assetService->register($request->validated());

        return redirect()->route('assets.index')
            ->with('success', __('Asset created successfully.'));
    }

    public function show(Asset $asset): Response
    {
        $asset->load([
            'item:id,name,name_ar,sku,item_type',
            'warehouse:id,name,name_ar',
            'epc:id,epc',
            'assignments' => fn($q) => $q->with(['assignedToUser:id,name', 'assignedBy:id,name'])->latest(),
            'maintenanceRecords' => fn($q) => $q->with(['performer:id,name', 'creator:id,name'])->latest(),
            'inspections' => fn($q) => $q->with(['inspectorUser:id,name'])->latest(),
            'warranties' => fn($q) => $q->latest(),
            'transfers' => fn($q) => $q->with(['fromWarehouse:id,name,name_ar', 'toWarehouse:id,name,name_ar', 'approver:id,name'])->latest(),
        ]);

        $users = User::select('id', 'name')->get();
        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
            'users' => $users,
            'warehouses' => $warehouses,
        ]);
    }

    public function edit(Asset $asset): Response
    {
        $items = Item::query()
            ->whereIn('item_type', [ItemType::MedicalDevice->value, ItemType::MedicalEquipment->value])
            ->select('id', 'name', 'name_ar', 'sku', 'item_type')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();

        return Inertia::render('Assets/Edit', [
            'asset' => $asset->load('item:id,name,name_ar'),
            'items' => $items,
            'warehouses' => $warehouses,
        ]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $asset->update($request->validated());

        return redirect()->route('assets.show', $asset)
            ->with('success', __('Asset updated successfully.'));
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', __('Asset deleted successfully.'));
    }

    public function assign(AssignAssetRequest $request, Asset $asset): RedirectResponse
    {
        $this->assetService->assign($asset->id, $request->validated());

        return back()->with('success', __('Asset assigned successfully.'));
    }

    public function returnAsset(ReturnAssetRequest $request, Asset $asset): RedirectResponse
    {
        // Find the active assignment
        $assignment = $asset->assignments()
            ->whereNull('returned_at')
            ->latest()
            ->firstOrFail();

        $this->assetService->returnAsset($assignment->id, $request->validated());

        return back()->with('success', __('Asset returned successfully.'));
    }

    public function dispose(Asset $asset): RedirectResponse
    {
        $reason = request()->validate(['reason' => 'required|string'])['reason'];

        $this->assetService->dispose($asset->id, $reason);

        return back()->with('success', __('Asset disposed successfully.'));
    }

    public function recordInspection(StoreAssetInspectionRequest $request, Asset $asset): RedirectResponse
    {
        $this->assetService->recordInspection($asset->id, $request->validated());

        return back()->with('success', __('Inspection recorded successfully.'));
    }
}
