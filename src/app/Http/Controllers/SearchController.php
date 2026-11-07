<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Batch;
use App\Models\Epc;
use App\Models\Item;
use App\Models\Receiving;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');

        if (!$query || strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'total' => 0,
            ]);
        }

        $results = [];

        // Search Items
        $items = Item::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('name_ar', 'like', "%{$query}%")
                ->orWhere('sku', 'like', "%{$query}%")
                ->orWhere('manufacturer', 'like', "%{$query}%");
        })
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type' => 'item',
                'id' => $item->id,
                'title' => $item->name,
                'subtitle' => $item->sku,
                'url' => route('items.items.show', $item),
            ]);

        $results = array_merge($results, $items->toArray());

        // Search Assets
        $assets = Asset::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('asset_code', 'like', "%{$query}%")
                ->orWhere('serial_number', 'like', "%{$query}%");
        })
            ->limit(5)
            ->get()
            ->map(fn($asset) => [
                'type' => 'asset',
                'id' => $asset->id,
                'title' => $asset->name,
                'subtitle' => $asset->asset_code,
                'url' => route('assets.show', $asset),
            ]);

        $results = array_merge($results, $assets->toArray());

        // Search EPCs
        $epcs = Epc::where('epc', 'like', "%{$query}%")
            ->with('item:id,name')
            ->limit(5)
            ->get()
            ->map(fn($epc) => [
                'type' => 'epc',
                'id' => $epc->id,
                'title' => $epc->epc,
                'subtitle' => $epc->item->name,
                'url' => route('rfid.epcs.show', $epc),
            ]);

        $results = array_merge($results, $epcs->toArray());

        // Search Batches
        $batches = Batch::where('batch_number', 'like', "%{$query}%")
            ->with('item:id,name')
            ->limit(5)
            ->get()
            ->map(fn($batch) => [
                'type' => 'batch',
                'id' => $batch->id,
                'title' => $batch->batch_number,
                'subtitle' => $batch->item->name,
                'url' => '#', // Batch detail page not implemented yet
            ]);

        $results = array_merge($results, $batches->toArray());

        // Search Receivings
        $receivings = Receiving::where('receiving_number', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($receiving) => [
                'type' => 'receiving',
                'id' => $receiving->id,
                'title' => $receiving->receiving_number,
                'subtitle' => 'Status: ' . ucfirst($receiving->status),
                'url' => route('inventory.receivings.show', $receiving),
            ]);

        $results = array_merge($results, $receivings->toArray());

        return response()->json([
            'results' => $results,
            'total' => count($results),
        ]);
    }
}
