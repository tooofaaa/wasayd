<?php

namespace App\Http\Controllers\Rfid;

use App\Enums\EpcStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rfid\GenerateEpcBatchRequest;
use App\Http\Requests\Rfid\StoreEpcRequest;
use App\Models\Epc;
use App\Models\Item;
use App\Models\Batch;
use App\Models\Warehouse;
use App\Services\Rfid\EpcService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EpcController extends Controller
{
    public function __construct(
        private EpcService $epcService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $warehouseId = $request->input('warehouse_id');
        $itemId = $request->input('item_id');

        $epcs = Epc::query()
            ->with(['item:id,name,name_ar,sku', 'batch:id,batch_number', 'warehouse:id,name,name_ar'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('epc', 'like', "%{$search}%")
                        ->orWhere('sgtin', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->when($warehouseId, fn($query, $warehouseId) => $query->where('warehouse_id', $warehouseId))
            ->when($itemId, fn($query, $itemId) => $query->where('item_id', $itemId))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();
        $items = Item::select('id', 'name', 'name_ar', 'sku')->get();

        return Inertia::render('Rfid/Epcs/Index', [
            'epcs' => $epcs,
            'warehouses' => $warehouses,
            'items' => $items,
            'statuses' => collect(EpcStatus::cases())->map(fn($status) => [
                'value' => $status->value,
                'label' => ucfirst(str_replace('_', ' ', $status->value)),
            ]),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
            ],
        ]);
    }

    public function create(): Response
    {
        $items = Item::select('id', 'name', 'name_ar', 'sku')->get();
        $batches = Batch::select('id', 'batch_number', 'item_id')->get();
        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();

        return Inertia::render('Rfid/Epcs/Create', [
            'items' => $items,
            'batches' => $batches,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreEpcRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['status'] = EpcStatus::Unassigned;

        Epc::create($data);

        return redirect()->route('rfid.epcs.index')
            ->with('success', 'EPC created successfully.');
    }

    public function show(Epc $epc): Response
    {
        $epc->load(['item', 'batch', 'warehouse']);
        $history = $this->epcService->getEpcHistory($epc);

        return Inertia::render('Rfid/Epcs/Show', [
            'epc' => $epc,
            'history' => $history,
        ]);
    }

    public function destroy(Epc $epc): RedirectResponse
    {
        $epc->delete();

        return redirect()->route('rfid.epcs.index')
            ->with('success', 'EPC deleted successfully.');
    }

    public function generateBatch(GenerateEpcBatchRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $epcs = $this->epcService->generateBatch(
                $validated['item_id'],
                $validated['quantity'],
                $validated['tag_type']
            );

            return redirect()->route('rfid.epcs.index')
                ->with('success', "Successfully generated {$validated['quantity']} EPCs.");
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to generate EPCs: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function commission(Epc $epc, Request $request): RedirectResponse
    {
        $request->validate([
            'reader_id' => ['nullable', 'exists:rfid_readers,id'],
        ]);

        try {
            $this->epcService->commissionEpc($epc, $request->input('reader_id'));

            return back()->with('success', 'EPC commissioned successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function decommission(Epc $epc, Request $request): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->epcService->decommissionEpc($epc, $request->input('reason'));

            return back()->with('success', 'EPC decommissioned successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
