<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreZoneRequest;
use App\Http\Requests\Locations\UpdateZoneRequest;
use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ZoneController extends Controller
{

    public function index(): Response
    {
        $search = request('search');
        $warehouseId = request('warehouse_id');

        $zones = Zone::query()
            ->with('warehouse:id,name,name_ar,code')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($warehouseId, fn($query, $warehouseId) => $query->where('warehouse_id', $warehouseId))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::select('id', 'name', 'name_ar', 'code')->get();

        return Inertia::render('Locations/Zones/Index', [
            'zones' => $zones,
            'warehouses' => $warehouses,
            'filters' => [
                'search' => $search,
                'warehouse_id' => $warehouseId,
            ],
        ]);
    }

    public function create(): Response
    {
        $warehouses = Warehouse::select('id', 'name', 'name_ar', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Zones/Create', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreZoneRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        Zone::create($data);

        return redirect()->route('locations.zones.index')
            ->with('success', 'Zone created successfully.');
    }

    public function edit(Zone $zone): Response
    {
        $zone->load('warehouse:id,name,name_ar,code');
        $warehouses = Warehouse::select('id', 'name', 'name_ar', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Zones/Edit', [
            'zone' => $zone,
            'warehouses' => $warehouses,
        ]);
    }

    public function update(UpdateZoneRequest $request, Zone $zone): RedirectResponse
    {
        $zone->update($request->validated());

        return redirect()->route('locations.zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        $zone->delete();

        return redirect()->route('locations.zones.index')
            ->with('success', 'Zone deleted successfully.');
    }
}
