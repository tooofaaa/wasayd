<?php

namespace App\Http\Controllers\Items;

use App\Enums\ItemType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Items\StoreItemRequest;
use App\Http\Requests\Items\UpdateItemRequest;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemSubcategory;
use App\Models\ItemUom;
use App\Services\ItemTypeRuleService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function __construct(
        protected ItemTypeRuleService $typeRuleService
    ) {
    }

    public function index(): Response
    {
        $search = request('search');
        $itemType = request('item_type');
        $categoryId = request('category_id');
        $status = request('status');

        $items = Item::query()
            ->with([
                'category:id,name,name_ar',
                'subcategory:id,name,name_ar',
                'baseUom:id,name,symbol',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('manufacturer', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($itemType, fn($query, $type) => $query->where('item_type', $type))
            ->when($categoryId, fn($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($status, fn($query, $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $categories = ItemCategory::select('id', 'name', 'name_ar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $itemTypes = collect(ItemType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'label_ar' => $type->labelAr(),
        ]);

        return Inertia::render('Items/Items/Index', [
            'items' => $items,
            'categories' => $categories,
            'itemTypes' => $itemTypes,
            'filters' => [
                'search' => $search,
                'item_type' => $itemType,
                'category_id' => $categoryId,
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        $categories = ItemCategory::select('id', 'name', 'name_ar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subcategories = ItemSubcategory::select('id', 'category_id', 'name', 'name_ar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $uoms = ItemUom::select('id', 'name', 'name_ar', 'code', 'symbol')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $itemTypes = collect(ItemType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'label_ar' => $type->labelAr(),
            'requires_batch_control' => $type->requiresBatchControl(),
            'requires_expiry_control' => $type->requiresExpiryControl(),
            'requires_serial_control' => $type->requiresSerialControl(),
        ]);

        return Inertia::render('Items/Items/Create', [
            'categories' => $categories,
            'subcategories' => $subcategories,
            'uoms' => $uoms,
            'itemTypes' => $itemTypes,
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Apply type-specific rules
        $data = $this->typeRuleService->evaluateRules($data['item_type'], $data);

        // Set company context
        $data['company_id'] = auth()->user()->company_id;
        $data['is_active'] = $data['is_active'] ?? true;

        Item::create($data);

        return redirect()->route('items.items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item): Response
    {
        $item->load([
            'category:id,name,name_ar,code',
            'subcategory:id,name,name_ar,code',
            'baseUom:id,name,name_ar,code,symbol',
            'barcodes',
            'attributes',
        ]);

        return Inertia::render('Items/Items/Show', [
            'item' => $item,
        ]);
    }

    public function edit(Item $item): Response
    {
        $item->load([
            'category:id,name,name_ar',
            'subcategory:id,name,name_ar',
            'baseUom:id,name,name_ar',
        ]);

        $categories = ItemCategory::select('id', 'name', 'name_ar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subcategories = ItemSubcategory::select('id', 'category_id', 'name', 'name_ar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $uoms = ItemUom::select('id', 'name', 'name_ar', 'code', 'symbol')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $itemTypes = collect(ItemType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'label_ar' => $type->labelAr(),
            'requires_batch_control' => $type->requiresBatchControl(),
            'requires_expiry_control' => $type->requiresExpiryControl(),
            'requires_serial_control' => $type->requiresSerialControl(),
        ]);

        return Inertia::render('Items/Items/Edit', [
            'item' => $item,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'uoms' => $uoms,
            'itemTypes' => $itemTypes,
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        $data = $request->validated();

        // Apply type-specific rules
        $data = $this->typeRuleService->evaluateRules($data['item_type'], $data);

        $item->update($data);

        return redirect()->route('items.items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        // Soft delete
        $item->delete();

        return redirect()->route('items.items.index')
            ->with('success', 'Item deleted successfully.');
    }
}
