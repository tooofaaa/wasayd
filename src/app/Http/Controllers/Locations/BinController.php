<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreBinRequest;
use App\Http\Requests\Locations\UpdateBinRequest;
use App\Models\Bin;
use App\Models\Shelf;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BinController extends Controller
{

    public function index(): Response
    {
        $search = request('search');
        $shelfId = request('shelf_id');

        $bins = Bin::query()
            ->with('shelf.rack.aisle.zone.warehouse:id,name,name_ar,code')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($shelfId, fn($query, $shelfId) => $query->where('shelf_id', $shelfId))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $shelves = Shelf::with('rack.aisle.zone.warehouse:id,name,name_ar,code')
            ->select('id', 'rack_id', 'name', 'code')
            ->get();

        return Inertia::render('Locations/Bins/Index', [
            'bins' => $bins,
            'shelves' => $shelves,
            'filters' => [
                'search' => $search,
                'shelf_id' => $shelfId,
            ],
        ]);
    }

    public function create(): Response
    {
        $shelves = Shelf::with('rack.aisle.zone.warehouse:id,name,name_ar,code')
            ->select('id', 'rack_id', 'name', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Bins/Create', [
            'shelves' => $shelves,
        ]);
    }

    public function store(StoreBinRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        Bin::create($data);

        return redirect()->route('locations.bins.index')
            ->with('success', 'Bin created successfully.');
    }

    public function edit(Bin $bin): Response
    {
        $bin->load('shelf.rack.aisle.zone.warehouse:id,name,name_ar,code');
        $shelves = Shelf::with('rack.aisle.zone.warehouse:id,name,name_ar,code')
            ->select('id', 'rack_id', 'name', 'code')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Locations/Bins/Edit', [
            'bin' => $bin,
            'shelves' => $shelves,
        ]);
    }

    public function update(UpdateBinRequest $request, Bin $bin): RedirectResponse
    {
        $bin->update($request->validated());

        return redirect()->route('locations.bins.index')
            ->with('success', 'Bin updated successfully.');
    }

    public function destroy(Bin $bin): RedirectResponse
    {
        $bin->delete();

        return redirect()->route('locations.bins.index')
            ->with('success', 'Bin deleted successfully.');
    }
}
