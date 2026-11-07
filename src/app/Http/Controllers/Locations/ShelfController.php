<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreShelfRequest;
use App\Http\Requests\Locations\UpdateShelfRequest;
use App\Models\Rack;
use App\Models\Shelf;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShelfController extends Controller
{

    public function index(): Response
    {
        $search = request('search');
        $rackId = request('rack_id');

        $shelves = Shelf::query()
            ->with('rack.aisle.zone.warehouse:id,name,name_ar,code')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($rackId, fn($query, $rackId) => $query->where('rack_id', $rackId))
            ->orderBy('level')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $racks = Rack::with('aisle.zone.warehouse:id,name,name_ar,code')
            ->select('id', 'aisle_id', 'name', 'code')
            ->get();

        return Inertia::render('Locations/Shelves/Index', [
            'shelves' => $shelves,
            'racks' => $racks,
            'filters' => [
                'search' => $search,
                'rack_id' => $rackId,
            ],
        ]);
    }

    public function create(): Response
    {
        $racks = Rack::with('aisle.zone.warehouse:id,name,name_ar,code')
            ->select('id', 'aisle_id', 'name', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Shelves/Create', [
            'racks' => $racks,
        ]);
    }

    public function store(StoreShelfRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        Shelf::create($data);

        return redirect()->route('locations.shelves.index')
            ->with('success', 'Shelf created successfully.');
    }

    public function edit(Shelf $shelf): Response
    {
        $shelf->load('rack.aisle.zone.warehouse:id,name,name_ar,code');
        $racks = Rack::with('aisle.zone.warehouse:id,name,name_ar,code')
            ->select('id', 'aisle_id', 'name', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Shelves/Edit', [
            'shelf' => $shelf,
            'racks' => $racks,
        ]);
    }

    public function update(UpdateShelfRequest $request, Shelf $shelf): RedirectResponse
    {
        $shelf->update($request->validated());

        return redirect()->route('locations.shelves.index')
            ->with('success', 'Shelf updated successfully.');
    }

    public function destroy(Shelf $shelf): RedirectResponse
    {
        $shelf->delete();

        return redirect()->route('locations.shelves.index')
            ->with('success', 'Shelf deleted successfully.');
    }
}
