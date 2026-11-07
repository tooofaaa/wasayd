<?php

namespace App\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\StoreItemCategoryRequest;
use App\Http\Requests\Items\UpdateItemCategoryRequest;
use App\Models\ItemCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemCategoryController extends Controller
{
    public function index(): Response
    {
        $search = request('search');

        $categories = ItemCategory::query()
            ->with('parent:id,name,name_ar')
            ->withCount('items')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Items/Categories/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        $parentCategories = ItemCategory::select('id', 'name', 'name_ar')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Items/Categories/Create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(StoreItemCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        ItemCategory::create($data);

        return redirect()->route('items.categories.index')
            ->with('success', 'Item category created successfully.');
    }

    public function edit(ItemCategory $category): Response
    {
        $category->load('parent:id,name,name_ar');

        $parentCategories = ItemCategory::select('id', 'name', 'name_ar')
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Items/Categories/Edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(UpdateItemCategoryRequest $request, ItemCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('items.categories.index')
            ->with('success', 'Item category updated successfully.');
    }

    public function destroy(ItemCategory $category): RedirectResponse
    {
        if ($category->items()->exists()) {
            return back()->with('error', 'Cannot delete category that has items assigned.');
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete category that has subcategories.');
        }

        $category->delete();

        return redirect()->route('items.categories.index')
            ->with('success', 'Item category deleted successfully.');
    }
}
