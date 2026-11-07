<?php

namespace App\Http\Controllers\Assets;

use App\Enums\TransferStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\StoreAssetTransferRequest;
use App\Models\Asset;
use App\Models\AssetTransfer;
use App\Models\Warehouse;
use App\Services\AssetService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AssetTransferController extends Controller
{
    public function __construct(
        protected AssetService $assetService
    ) {
    }

    public function index(): Response
    {
        $search = request('search');
        $status = request('status');

        $transfers = AssetTransfer::query()
            ->with([
                'asset:id,asset_code,name,name_ar',
                'fromWarehouse:id,name,name_ar',
                'toWarehouse:id,name,name_ar',
                'approver:id,name',
                'creator:id,name',
            ])
            ->when($search, function ($query, $search) {
                $query->whereHas('asset', function ($q) use ($search) {
                    $q->where('asset_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $statuses = collect(TransferStatus::cases())->map(fn($status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ]);

        return Inertia::render('Assets/Transfers/Index', [
            'transfers' => $transfers,
            'statuses' => $statuses,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        $assets = Asset::query()
            ->with('warehouse:id,name,name_ar')
            ->select('id', 'asset_code', 'name', 'name_ar', 'current_warehouse_id')
            ->whereNotIn('status', ['disposed', 'decommissioned'])
            ->orderBy('asset_code')
            ->get();

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();

        return Inertia::render('Assets/Transfers/Create', [
            'assets' => $assets,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreAssetTransferRequest $request): RedirectResponse
    {
        $this->assetService->transfer($request->asset_id, $request->validated());

        return redirect()->route('assets.transfers.index')
            ->with('success', __('Transfer request created successfully.'));
    }

    public function approve(AssetTransfer $transfer): RedirectResponse
    {
        $this->assetService->approveTransfer($transfer->id);

        return back()->with('success', __('Transfer approved successfully.'));
    }

    public function complete(AssetTransfer $transfer): RedirectResponse
    {
        $this->assetService->completeTransfer($transfer->id);

        return back()->with('success', __('Transfer completed successfully.'));
    }
}
