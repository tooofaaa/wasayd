<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\AdjustmentStatus;
use App\Enums\AdjustmentType;
use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\StockCount;
use App\Models\Warehouse;
use App\Models\Item;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private StockAdjustmentService $adjustmentService
    ) {}

    public function index(Request $request): Response
    {
        $query = StockAdjustment::query()
            ->with(['requester', 'approver', 'stockCount'])
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('adjustment_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $adjustments = $query->latest()->paginate(15);

        return Inertia::render('Inventory/StockAdjustment/Index', [
            'adjustments' => $adjustments,
            'filters' => $request->only(['status', 'type', 'search']),
            'statuses' => AdjustmentStatus::cases(),
            'types' => AdjustmentType::cases(),
        ]);
    }

    public function create(Request $request): Response
    {
        $stockCountId = $request->get('stock_count_id');
        $stockCount = null;

        if ($stockCountId) {
            $stockCount = StockCount::with('lines')->findOrFail($stockCountId);
        }

        return Inertia::render('Inventory/StockAdjustment/Create', [
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'items' => Item::where('company_id', auth()->user()->company_id)
                ->select('id', 'sku', 'name')->get(),
            'adjustmentTypes' => AdjustmentType::cases(),
            'stockCount' => $stockCount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('stock_count_id')) {
            $adjustment = $this->adjustmentService->createFromCount($request->stock_count_id);
        } else {
            $validated = $request->validate([
                'adjustment_type' => 'required|in:manual,damage,expiry',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
                'lines' => 'required|array|min:1',
                'lines.*.item_id' => 'required|exists:items,id',
                'lines.*.warehouse_id' => 'required|exists:warehouses,id',
                'lines.*.batch_id' => 'nullable|exists:batches,id',
                'lines.*.bin_id' => 'nullable|exists:bins,id',
                'lines.*.system_quantity' => 'required|numeric',
                'lines.*.adjusted_quantity' => 'required|numeric',
                'lines.*.reason' => 'nullable|string',
            ]);

            $adjustment = $this->adjustmentService->createManual($validated);
        }

        return redirect()->route('inventory.stock-adjustments.show', $adjustment)
            ->with('success', __('Stock adjustment created successfully'));
    }

    public function show(StockAdjustment $stockAdjustment): Response
    {
        $stockAdjustment->load([
            'requester',
            'approver',
            'stockCount',
            'lines.item',
            'lines.batch',
            'lines.bin',
            'lines.warehouse',
            'approval.steps.user',
            'approval.steps.actedBy'
        ]);

        return Inertia::render('Inventory/StockAdjustment/Show', [
            'adjustment' => $stockAdjustment,
        ]);
    }

    public function submitForApproval(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== AdjustmentStatus::PendingApproval) {
            return back()->with('error', __('Adjustment has already been submitted'));
        }

        $this->adjustmentService->submitForApproval($stockAdjustment);

        return back()->with('success', __('Adjustment submitted for approval'));
    }

    public function approve(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== AdjustmentStatus::PendingApproval) {
            return back()->with('error', __('Only pending adjustments can be approved'));
        }

        $stockAdjustment->update([
            'status' => AdjustmentStatus::Approved,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', __('Adjustment approved successfully'));
    }

    public function reject(Request $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== AdjustmentStatus::PendingApproval) {
            return back()->with('error', __('Only pending adjustments can be rejected'));
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $stockAdjustment->update([
            'status' => AdjustmentStatus::Rejected,
            'notes' => ($stockAdjustment->notes ? $stockAdjustment->notes . "\n\n" : '')
                . "Rejected: {$validated['reason']}",
        ]);

        return back()->with('success', __('Adjustment rejected'));
    }

    public function apply(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== AdjustmentStatus::Approved) {
            return back()->with('error', __('Only approved adjustments can be applied'));
        }

        $this->adjustmentService->applyAdjustment($stockAdjustment);

        return back()->with('success', __('Adjustment applied successfully. Stock updated.'));
    }

    public function destroy(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== AdjustmentStatus::PendingApproval) {
            return back()->with('error', __('Only pending adjustments can be deleted'));
        }

        $stockAdjustment->delete();

        return redirect()->route('inventory.stock-adjustments.index')
            ->with('success', __('Adjustment deleted successfully'));
    }
}
