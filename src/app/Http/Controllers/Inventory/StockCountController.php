<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\StockCountStatus;
use App\Enums\StockCountType;
use App\Http\Controllers\Controller;
use App\Models\StockCount;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\ItemCategory;
use App\Services\StockCountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockCountController extends Controller
{
    public function __construct(
        private StockCountService $stockCountService
    ) {}

    public function index(Request $request): Response
    {
        $query = StockCount::query()
            ->with(['warehouse', 'creator', 'assignee'])
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('count_type', $request->type);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('count_number', 'like', "%{$search}%")
                    ->orWhereHas('warehouse', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $stockCounts = $query->latest()->paginate(15);

        return Inertia::render('Inventory/StockCount/Index', [
            'stockCounts' => $stockCounts,
            'filters' => $request->only(['status', 'type', 'warehouse_id', 'search']),
            'statuses' => StockCountStatus::cases(),
            'types' => StockCountType::cases(),
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inventory/StockCount/Create', [
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'users' => User::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'zones' => Zone::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'categories' => ItemCategory::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'countTypes' => StockCountType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'count_type' => 'required|in:full,cycle,spot,rfid',
            'planned_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'exists:zones,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:item_categories,id',
            'notes' => 'nullable|string',
        ]);

        $stockCount = $this->stockCountService->createCount($validated);

        return redirect()->route('inventory.stock-counts.show', $stockCount)
            ->with('success', __('Stock count created successfully'));
    }

    public function show(StockCount $stockCount): Response
    {
        $stockCount->load([
            'warehouse',
            'creator',
            'assignee',
            'reviewer',
            'lines.item',
            'lines.batch',
            'lines.bin',
            'lines.counter'
        ]);

        // Calculate variance summary
        $lines = $stockCount->lines;
        $summary = [
            'total_items' => $lines->count(),
            'matched' => $lines->where('variance', 0)->count(),
            'positive_variance' => $lines->where('variance', '>', 0)->count(),
            'negative_variance' => $lines->where('variance', '<', 0)->count(),
            'uncounted' => $lines->whereNull('counted_quantity')->count(),
        ];

        return Inertia::render('Inventory/StockCount/Show', [
            'stockCount' => $stockCount,
            'summary' => $summary,
        ]);
    }

    public function edit(StockCount $stockCount): Response
    {
        if ($stockCount->status !== StockCountStatus::Planned) {
            return redirect()->route('inventory.stock-counts.show', $stockCount)
                ->with('error', __('Only planned counts can be edited'));
        }

        return Inertia::render('Inventory/StockCount/Edit', [
            'stockCount' => $stockCount,
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'users' => User::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'zones' => Zone::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'categories' => ItemCategory::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'countTypes' => StockCountType::cases(),
        ]);
    }

    public function update(Request $request, StockCount $stockCount): RedirectResponse
    {
        if ($stockCount->status !== StockCountStatus::Planned) {
            return redirect()->route('inventory.stock-counts.show', $stockCount)
                ->with('error', __('Only planned counts can be edited'));
        }

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'count_type' => 'required|in:full,cycle,spot,rfid',
            'planned_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'zone_ids' => 'nullable|array',
            'category_ids' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $stockCount->update($validated);

        return redirect()->route('inventory.stock-counts.show', $stockCount)
            ->with('success', __('Stock count updated successfully'));
    }

    public function start(StockCount $stockCount): RedirectResponse
    {
        if ($stockCount->status !== StockCountStatus::Planned) {
            return back()->with('error', __('Only planned counts can be started'));
        }

        $this->stockCountService->startCount($stockCount);

        return redirect()->route('inventory.stock-counts.show', $stockCount)
            ->with('success', __('Stock count started successfully'));
    }

    public function recordLine(Request $request, StockCount $stockCount): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'batch_id' => 'nullable|exists:batches,id',
            'bin_id' => 'nullable|exists:bins,id',
            'counted_quantity' => 'required|numeric|min:0',
            'count_method' => 'nullable|in:manual,barcode,rfid',
            'notes' => 'nullable|string',
        ]);

        $this->stockCountService->recordCountLine($stockCount->id, $validated);

        return back()->with('success', __('Count recorded successfully'));
    }

    public function submit(StockCount $stockCount): RedirectResponse
    {
        if (!in_array($stockCount->status, [StockCountStatus::InProgress, StockCountStatus::Counting])) {
            return back()->with('error', __('Invalid status for submission'));
        }

        $this->stockCountService->submitCount($stockCount);

        return redirect()->route('inventory.stock-counts.show', $stockCount)
            ->with('success', __('Stock count submitted for review'));
    }

    public function approve(StockCount $stockCount): RedirectResponse
    {
        if ($stockCount->status !== StockCountStatus::Submitted) {
            return back()->with('error', __('Only submitted counts can be approved'));
        }

        $this->stockCountService->approveCount($stockCount);

        return redirect()->route('inventory.stock-counts.show', $stockCount)
            ->with('success', __('Stock count approved successfully'));
    }

    public function destroy(StockCount $stockCount): RedirectResponse
    {
        if ($stockCount->status !== StockCountStatus::Planned) {
            return back()->with('error', __('Only planned counts can be deleted'));
        }

        $stockCount->delete();

        return redirect()->route('inventory.stock-counts.index')
            ->with('success', __('Stock count deleted successfully'));
    }
}
