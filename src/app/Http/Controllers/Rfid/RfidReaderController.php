<?php

namespace App\Http\Controllers\Rfid;

use App\Enums\RfidReaderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rfid\StoreRfidReaderRequest;
use App\Http\Requests\Rfid\UpdateRfidReaderRequest;
use App\Models\RfidReader;
use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RfidReaderController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $warehouseId = $request->input('warehouse_id');

        $readers = RfidReader::query()
            ->with(['warehouse:id,name,name_ar', 'zone:id,name,name_ar'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('reader_id', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->when($warehouseId, fn($query, $warehouseId) => $query->where('warehouse_id', $warehouseId))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();

        return Inertia::render('Rfid/Readers/Index', [
            'readers' => $readers,
            'warehouses' => $warehouses,
            'statuses' => collect(RfidReaderStatus::cases())->map(fn($status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ]),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'warehouse_id' => $warehouseId,
            ],
        ]);
    }

    public function create(): Response
    {
        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();
        $zones = Zone::select('id', 'name', 'name_ar', 'warehouse_id')->get();

        return Inertia::render('Rfid/Readers/Create', [
            'warehouses' => $warehouses,
            'zones' => $zones,
            'statuses' => collect(RfidReaderStatus::cases())->map(fn($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    public function store(StoreRfidReaderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        RfidReader::create($data);

        return redirect()->route('rfid.readers.index')
            ->with('success', 'RFID Reader created successfully.');
    }

    public function show(RfidReader $reader): Response
    {
        $reader->load(['warehouse', 'zone']);

        // Get recent read events
        $recentReads = $reader->readEvents()
            ->with('epc:id,epc,item_id')
            ->orderBy('first_read_at', 'desc')
            ->limit(50)
            ->get();

        return Inertia::render('Rfid/Readers/Show', [
            'reader' => $reader,
            'recentReads' => $recentReads,
            'isOnline' => $reader->isOnline(),
        ]);
    }

    public function edit(RfidReader $reader): Response
    {
        $warehouses = Warehouse::select('id', 'name', 'name_ar')->get();
        $zones = Zone::select('id', 'name', 'name_ar', 'warehouse_id')->get();

        return Inertia::render('Rfid/Readers/Edit', [
            'reader' => $reader,
            'warehouses' => $warehouses,
            'zones' => $zones,
            'statuses' => collect(RfidReaderStatus::cases())->map(fn($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    public function update(UpdateRfidReaderRequest $request, RfidReader $reader): RedirectResponse
    {
        $reader->update($request->validated());

        return redirect()->route('rfid.readers.index')
            ->with('success', 'RFID Reader updated successfully.');
    }

    public function destroy(RfidReader $reader): RedirectResponse
    {
        $reader->delete();

        return redirect()->route('rfid.readers.index')
            ->with('success', 'RFID Reader deleted successfully.');
    }
}
