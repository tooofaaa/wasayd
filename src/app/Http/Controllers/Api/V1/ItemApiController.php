<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 25);
        $search = $request->input('search');
        $itemType = $request->input('item_type');
        $categoryId = $request->input('category_id');

        $items = Item::query()
            ->with([
                'category:id,name,name_ar',
                'subcategory:id,name,name_ar',
                'baseUom:id,name,symbol',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($itemType, fn($q, $type) => $q->where('item_type', $type))
            ->when($categoryId, fn($q, $id) => $q->where('category_id', $id))
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($items);
    }

    public function show(Item $item): JsonResponse
    {
        $item->load([
            'category:id,name,name_ar',
            'subcategory:id,name,name_ar',
            'baseUom:id,name,name_ar,symbol',
            'barcodes',
            'stockBalances.warehouse:id,name',
        ]);

        return response()->json([
            'data' => $item,
        ]);
    }
}
