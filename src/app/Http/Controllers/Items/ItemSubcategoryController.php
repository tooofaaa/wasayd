<?php

namespace App\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\StoreItemSubcategoryRequest;
use App\Http\Requests\Items\UpdateItemSubcategoryRequest;
use App\Models\ItemCategory;
use App\Models\ItemSubcategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemSubcategoryController extends Controller
{
    public function index(): Response
    {
        $search = request('search');
        $categoryId = request('category_id');

        $subcategories = ItemSubcategory::query()
            ->with('category:id,name,name_ar')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = ItemCategory::select('id', 'name', 'name_ar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Items/Subcategories/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function create(): Response
    {
        $categories = ItemCategory::select('id', 'name', 'name_ar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Items/Subcategories/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreItemSubcategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        ItemSubcategory::create($data);

        return redirect()->route('items.subcategories.index')
            ->with('success', 'Item subcategory created successfully.');
    }

    public function edit(ItemSubcategory $subcategory): Response
    {
        $subcategory->load('category:id,name,name_ar');

        $categories = ItemCategory::select('id', 'name', 'name_ar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Items/Subcategories/Edit', [
            'subcategory' => $subcategory,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateItemSubcategoryRequest $request, ItemSubcategory $subcategory): RedirectResponse
    {
        $subcategory->update($request->validated());

        return redirect()->route('items.subcategories.index')
            ->with('success', 'Item subcategory updated successfully.');
    }

    public function destroy(ItemSubcategory $subcategory): RedirectResponse
    {
        $subcategory->delete();

        return redirect()->route('items.subcategories.index')
            ->with('success', 'Item subcategory deleted successfully.');
    }
}
