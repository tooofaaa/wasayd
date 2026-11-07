<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\ReceivingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ReceiveLineRequest;
use App\Http\Requests\Inventory\StoreReceivingLineRequest;
use App\Http\Requests\Inventory\StoreReceivingRequest;
use App\Models\Receiving;
use App\Models\ReceivingLine;
use App\Models\Warehouse;
use App\Services\ReceivingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReceivingController extends Controller
{
    public function __construct(
        private ReceivingService $receivingService
    ) {}

    /**
     * Display a listing of receivings
     */
    public function index(Request $request): Response
    {
        $query = Receiving::query()
            ->with(['warehouse', 'createdBy'])
            ->where('company_id', auth()->user()->company_id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('receiving_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('receiving_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receiving_number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('purchase_order_number', 'like', "%{$search}%");
            });
        }

        $receivings = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Inventory/Receiving/Index', [
            'receivings' => $receivings,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'search']),
            'statuses' => collect(ReceivingStatus::cases())->map(fn ($status) => [
                'value' => $status->value,
                'label' => ucfirst(str_replace('_', ' ', $status->value)),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new receiving
     */
    public function create(): Response
    {
        $warehouses = Warehouse::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->select('id', 'name', 'code')
            ->get();

        return Inertia::render('Inventory/Receiving/Create', [
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store a newly created receiving
     */
    public function store(StoreReceivingRequest $request): RedirectResponse
    {
        $receiving = $this->receivingService->createReceiving($request->validated());

        return redirect()
            ->route('inventory.receivings.show', $receiving)
            ->with('success', 'Receiving created successfully.');
    }

    /**
     * Display the specified receiving
     */
    public function show(Receiving $receiving): Response
    {
        $this->authorize('view', $receiving);

        $receiving->load([
            'warehouse',
            'lines.item.baseUom',
            'lines.batch',
            'lines.uom',
            'createdBy',
        ]);

        return Inertia::render('Inventory/Receiving/Show', [
            'receiving' => $receiving,
        ]);
    }

    /**
     * Add a line to the receiving
     */
    public function addLine(Receiving $receiving, StoreReceivingLineRequest $request): RedirectResponse
    {
        $this->authorize('update', $receiving);

        try {
            $this->receivingService->addLine($receiving, $request->validated());

            return back()->with('success', 'Line added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Receive a specific line
     */
    public function receiveLine(Receiving $receiving, ReceivingLine $line, ReceiveLineRequest $request): RedirectResponse
    {
        $this->authorize('update', $receiving);

        try {
            $this->receivingService->receiveLine($line, $request->validated());

            return back()->with('success', 'Line received successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Complete the receiving
     */
    public function complete(Receiving $receiving): RedirectResponse
    {
        $this->authorize('update', $receiving);

        try {
            $this->receivingService->completeReceiving($receiving);

            return redirect()
                ->route('inventory.receivings.show', $receiving)
                ->with('success', 'Receiving completed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified receiving (only drafts)
     */
    public function destroy(Receiving $receiving): RedirectResponse
    {
        $this->authorize('delete', $receiving);

        if ($receiving->status !== ReceivingStatus::Draft) {
            return back()->withErrors(['error' => 'Only draft receivings can be deleted.']);
        }

        $receiving->delete();

        return redirect()
            ->route('inventory.receivings.index')
            ->with('success', 'Receiving deleted successfully.');
    }
}
