<?php

namespace App\Http\Controllers\Operations;

use App\Enums\QuarantineStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResolveQuarantineRequest;
use App\Http\Requests\StoreQuarantineRequest;
use App\Models\Batch;
use App\Models\Bin;
use App\Models\Item;
use App\Models\Quarantine;
use App\Models\Warehouse;
use App\Services\QuarantineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuarantineController extends Controller
{
    public function __construct(
        private QuarantineService $quarantineService
    ) {}

    public function index(Request $request): Response
    {
        $query = Quarantine::query()
            ->with(['item', 'batch', 'warehouse', 'bin', 'quarantinedBy'])
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quarantine_number', 'like', "%{$search}%")
                    ->orWhere('reason_code', 'like', "%{$search}%");
            });
        }

        $quarantines = $query->latest()->paginate(15);

        return Inertia::render('Quarantines/Index', [
            'quarantines' => $quarantines,
            'filters' => $request->only(['status', 'search']),
            'statuses' => QuarantineStatus::cases(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Quarantines/Create', [
            'items' => Item::where('company_id', auth()->user()->company_id)
                ->select('id', 'sku', 'name')->get(),
            'batches' => Batch::where('company_id', auth()->user()->company_id)
                ->select('id', 'batch_number', 'item_id')->get(),
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'bins' => Bin::whereHas('shelf.rack.aisle.zone', function ($query) {
                $query->where('company_id', auth()->user()->company_id);
            })->select('id', 'name')->get(),
        ]);
    }

    public function store(StoreQuarantineRequest $request): RedirectResponse
    {
        $quarantine = $this->quarantineService->createQuarantine($request->validated());

        return redirect()->route('operations.quarantines.show', $quarantine)
            ->with('success', 'Quarantine created successfully');
    }

    public function show(Quarantine $quarantine): Response
    {
        $quarantine->load(['item', 'batch', 'warehouse', 'bin', 'quarantinedBy', 'resolvedBy']);

        return Inertia::render('Quarantines/Show', [
            'quarantine' => $quarantine,
        ]);
    }

    public function release(Quarantine $quarantine, ResolveQuarantineRequest $request): RedirectResponse
    {
        $this->quarantineService->releaseQuarantine(
            $quarantine->id,
            $request->validated()['resolution_notes'] ?? null
        );

        return redirect()->route('operations.quarantines.show', $quarantine)
            ->with('success', 'Quarantine released successfully');
    }

    public function dispose(Quarantine $quarantine, ResolveQuarantineRequest $request): RedirectResponse
    {
        $this->quarantineService->disposeQuarantine(
            $quarantine->id,
            $request->validated()['resolution_notes'] ?? null
        );

        return redirect()->route('operations.quarantines.show', $quarantine)
            ->with('success', 'Quarantine disposed successfully');
    }
}
