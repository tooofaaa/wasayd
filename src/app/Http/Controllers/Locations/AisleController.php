<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreAisleRequest;
use App\Http\Requests\Locations\UpdateAisleRequest;
use App\Models\Aisle;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AisleController extends Controller
{

    public function index(): Response
    {
        $search = request('search');
        $zoneId = request('zone_id');

        $aisles = Aisle::query()
            ->with('zone.warehouse:id,name,name_ar,code')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($zoneId, fn($query, $zoneId) => $query->where('zone_id', $zoneId))
            ->orderBy('sequence')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $zones = Zone::with('warehouse:id,name,name_ar,code')
            ->select('id', 'warehouse_id', 'name', 'name_ar', 'code')
            ->get();

        return Inertia::render('Locations/Aisles/Index', [
            'aisles' => $aisles,
            'zones' => $zones,
            'filters' => [
                'search' => $search,
                'zone_id' => $zoneId,
            ],
        ]);
    }

    public function create(): Response
    {
        $zones = Zone::with('warehouse:id,name,name_ar,code')
            ->select('id', 'warehouse_id', 'name', 'name_ar', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Aisles/Create', [
            'zones' => $zones,
        ]);
    }

    public function store(StoreAisleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        Aisle::create($data);

        return redirect()->route('locations.aisles.index')
            ->with('success', 'Aisle created successfully.');
    }

    public function edit(Aisle $aisle): Response
    {
        $aisle->load('zone.warehouse:id,name,name_ar,code');
        $zones = Zone::with('warehouse:id,name,name_ar,code')
            ->select('id', 'warehouse_id', 'name', 'name_ar', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Aisles/Edit', [
            'aisle' => $aisle,
            'zones' => $zones,
        ]);
    }

    public function update(UpdateAisleRequest $request, Aisle $aisle): RedirectResponse
    {
        $aisle->update($request->validated());

        return redirect()->route('locations.aisles.index')
            ->with('success', 'Aisle updated successfully.');
    }

    public function destroy(Aisle $aisle): RedirectResponse
    {
        $aisle->delete();

        return redirect()->route('locations.aisles.index')
            ->with('success', 'Aisle deleted successfully.');
    }
}
