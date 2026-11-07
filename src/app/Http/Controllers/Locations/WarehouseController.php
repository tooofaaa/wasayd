<?php

namespace App\Http\Controllers\Locations;

use App\Enums\WarehouseType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreWarehouseRequest;
use App\Http\Requests\Locations\UpdateWarehouseRequest;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{

    public function index(): Response
    {
        $search = request('search');
        $branchId = request('branch_id');
        $type = request('type');

        $warehouses = Warehouse::query()
            ->with('branch:id,name,name_ar')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($branchId, fn($query, $branchId) => $query->where('branch_id', $branchId))
            ->when($type, fn($query, $type) => $query->where('type', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::select('id', 'name', 'name_ar')->get();

        return Inertia::render('Locations/Warehouses/Index', [
            'warehouses' => $warehouses,
            'branches' => $branches,
            'warehouseTypes' => collect(WarehouseType::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => ucfirst(str_replace('_', ' ', $type->value)),
            ]),
            'filters' => [
                'search' => $search,
                'branch_id' => $branchId,
                'type' => $type,
            ],
        ]);
    }

    public function create(): Response
    {
        $branches = Branch::select('id', 'name', 'name_ar')->where('is_active', true)->get();

        return Inertia::render('Locations/Warehouses/Create', [
            'branches' => $branches,
            'warehouseTypes' => collect(WarehouseType::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => ucfirst(str_replace('_', ' ', $type->value)),
            ]),
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        Warehouse::create($data);

        return redirect()->route('locations.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse): Response
    {
        $warehouse->load([
            'branch:id,name,name_ar',
            'zones:id,warehouse_id,name,name_ar,code,type,is_active',
        ]);

        return Inertia::render('Locations/Warehouses/Show', [
            'warehouse' => $warehouse,
        ]);
    }

    public function edit(Warehouse $warehouse): Response
    {
        $warehouse->load('branch:id,name,name_ar');
        $branches = Branch::select('id', 'name', 'name_ar')->where('is_active', true)->get();

        return Inertia::render('Locations/Warehouses/Edit', [
            'warehouse' => $warehouse,
            'branches' => $branches,
            'warehouseTypes' => collect(WarehouseType::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => ucfirst(str_replace('_', ' ', $type->value)),
            ]),
        ]);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validated());

        return redirect()->route('locations.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return redirect()->route('locations.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
