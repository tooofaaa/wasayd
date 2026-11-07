<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockBalance;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class ImportExportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ImportExport/Index');
    }

    public function exportItems(): HttpResponse
    {
        $items = Item::with([
            'category:id,name',
            'subcategory:id,name',
            'baseUom:id,name,symbol',
        ])->get();

        $csv = "SKU,Name,Name (Arabic),Category,Subcategory,Type,Unit Cost,Unit Price,Min Stock,Max Stock,Reorder Point,Active\n";

        foreach ($items as $item) {
            $csv .= implode(',', [
                $this->escapeCsv($item->sku),
                $this->escapeCsv($item->name),
                $this->escapeCsv($item->name_ar),
                $this->escapeCsv($item->category?->name),
                $this->escapeCsv($item->subcategory?->name),
                $this->escapeCsv($item->item_type->label()),
                $item->unit_cost ?? 0,
                $item->unit_price ?? 0,
                $item->min_stock ?? 0,
                $item->max_stock ?? 0,
                $item->reorder_point ?? 0,
                $item->is_active ? 'Yes' : 'No',
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="items-' . now()->format('Y-m-d') . '.csv"');
    }

    public function exportStock(): HttpResponse
    {
        $stocks = StockBalance::with([
            'item:id,name,sku',
            'warehouse:id,name',
        ])->get();

        $csv = "SKU,Item Name,Warehouse,On Hand,Reserved,Available\n";

        foreach ($stocks as $stock) {
            $csv .= implode(',', [
                $this->escapeCsv($stock->item->sku),
                $this->escapeCsv($stock->item->name),
                $this->escapeCsv($stock->warehouse->name),
                $stock->total_on_hand,
                $stock->total_reserved,
                $stock->total_available,
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="stock-' . now()->format('Y-m-d') . '.csv"');
    }

    public function showImportForm(): Response
    {
        return Inertia::render('Admin/ImportExport/ImportItems', [
            'preview' => session('import_preview'),
        ]);
    }

    public function importItems(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'confirm' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $csv = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csv);

        // Validate CSV structure
        $expectedHeaders = ['sku', 'name', 'name_ar', 'category', 'item_type', 'unit_cost', 'min_stock'];
        $missingHeaders = array_diff($expectedHeaders, array_map('strtolower', $header));

        if (!empty($missingHeaders)) {
            return back()->withErrors([
                'file' => 'CSV is missing required columns: ' . implode(', ', $missingHeaders),
            ]);
        }

        $preview = [];
        $errors = [];

        foreach ($csv as $index => $row) {
            $rowData = array_combine(array_map('strtolower', $header), $row);
            $rowNumber = $index + 2; // +2 because array is 0-indexed and we removed header

            // Validate row
            $validator = Validator::make($rowData, [
                'sku' => 'required|string|max:50',
                'name' => 'required|string|max:255',
                'item_type' => 'required|string',
                'unit_cost' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                continue;
            }

            $preview[] = [
                'row' => $rowNumber,
                'sku' => $rowData['sku'],
                'name' => $rowData['name'],
                'name_ar' => $rowData['name_ar'] ?? null,
                'item_type' => $rowData['item_type'],
                'unit_cost' => $rowData['unit_cost'] ?? 0,
                'min_stock' => $rowData['min_stock'] ?? 0,
                'status' => 'pending',
            ];
        }

        // If confirm is true, process the import
        if ($request->boolean('confirm') && empty($errors)) {
            DB::beginTransaction();
            try {
                foreach ($preview as $itemData) {
                    Item::updateOrCreate(
                        ['sku' => $itemData['sku']],
                        [
                            'name' => $itemData['name'],
                            'name_ar' => $itemData['name_ar'],
                            'item_type' => $itemData['item_type'],
                            'unit_cost' => $itemData['unit_cost'],
                            'min_stock' => $itemData['min_stock'],
                            'company_id' => auth()->user()->company_id,
                            'is_active' => true,
                        ]
                    );
                }

                DB::commit();

                return redirect()->route('import-export.index')
                    ->with('success', count($preview) . ' items imported successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
            }
        }

        // Return preview
        return back()->with([
            'import_preview' => [
                'items' => $preview,
                'errors' => $errors,
                'total' => count($preview),
            ],
        ]);
    }

    private function escapeCsv(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // If value contains comma, quote, or newline, wrap in quotes and escape quotes
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }
}
