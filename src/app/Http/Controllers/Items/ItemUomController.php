<?php

namespace App\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use App\Models\ItemUom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemUomController extends Controller
{
    public function index(): Response
    {
        $search = request('search');

        $uoms = ItemUom::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('symbol', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Items/Uoms/Index', [
            'uoms' => $uoms,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Items/Uoms/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'name_ar' => 'nullable|string|max:100',
            'code' => 'required|string|max:20|unique:item_uoms,code',
            'symbol' => 'nullable|string|max:10',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        ItemUom::create($data);

        return redirect()->route('items.uoms.index')
            ->with('success', 'Unit of Measure created successfully.');
    }

    public function edit(ItemUom $uom): Response
    {
        return Inertia::render('Items/Uoms/Edit', [
            'uom' => $uom,
        ]);
    }

    public function update(Request $request, ItemUom $uom): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'name_ar' => 'nullable|string|max:100',
            'code' => 'required|string|max:20|unique:item_uoms,code,' . $uom->id,
            'symbol' => 'nullable|string|max:10',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $uom->update($data);

        return redirect()->route('items.uoms.index')
            ->with('success', 'Unit of Measure updated successfully.');
    }

    public function destroy(ItemUom $uom): RedirectResponse
    {
        $uom->delete();

        return redirect()->route('items.uoms.index')
            ->with('success', 'Unit of Measure deleted successfully.');
    }
}
