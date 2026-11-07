<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreRackRequest;
use App\Http\Requests\Locations\UpdateRackRequest;
use App\Models\Aisle;
use App\Models\Rack;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RackController extends Controller
{

    public function index(): Response
    {
        $search = request('search');
        $aisleId = request('aisle_id');

        $racks = Rack::query()
            ->with('aisle.zone.warehouse:id,name,name_ar,code')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($aisleId, fn($query, $aisleId) => $query->where('aisle_id', $aisleId))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $aisles = Aisle::with('zone.warehouse:id,name,name_ar,code')
            ->select('id', 'zone_id', 'name', 'code')
            ->get();

        return Inertia::render('Locations/Racks/Index', [
            'racks' => $racks,
            'aisles' => $aisles,
            'filters' => [
                'search' => $search,
                'aisle_id' => $aisleId,
            ],
        ]);
    }

    public function create(): Response
    {
        $aisles = Aisle::with('zone.warehouse:id,name,name_ar,code')
            ->select('id', 'zone_id', 'name', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Racks/Create', [
            'aisles' => $aisles,
        ]);
    }

    public function store(StoreRackRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        Rack::create($data);

        return redirect()->route('locations.racks.index')
            ->with('success', 'Rack created successfully.');
    }

    public function edit(Rack $rack): Response
    {
        $rack->load('aisle.zone.warehouse:id,name,name_ar,code');
        $aisles = Aisle::with('zone.warehouse:id,name,name_ar,code')
            ->select('id', 'zone_id', 'name', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Racks/Edit', [
            'rack' => $rack,
            'aisles' => $aisles,
        ]);
    }

    public function update(UpdateRackRequest $request, Rack $rack): RedirectResponse
    {
        $rack->update($request->validated());

        return redirect()->route('locations.racks.index')
            ->with('success', 'Rack updated successfully.');
    }

    public function destroy(Rack $rack): RedirectResponse
    {
        $rack->delete();

        return redirect()->route('locations.racks.index')
            ->with('success', 'Rack deleted successfully.');
    }
}
