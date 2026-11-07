<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Models\Item;
use App\Services\InventoryTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionLedgerController extends Controller
{
    public function __construct(
        private InventoryTransactionService $transactionService
    ) {}

    public function index(Request $request): Response
    {
        $query = InventoryTransaction::query()
            ->with(['item', 'batch', 'fromWarehouse', 'toWarehouse', 'createdBy'])
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('warehouse_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id)
                    ->orWhere('to_warehouse_id', $request->warehouse_id);
            });
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('item', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $transactions = $query->latest('transaction_date')->paginate(20);

        return Inertia::render('Inventory/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['type', 'warehouse_id', 'item_id', 'date_from', 'date_to', 'search']),
            'transactionTypes' => TransactionType::cases(),
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'items' => Item::where('company_id', auth()->user()->company_id)
                ->select('id', 'sku', 'name')->take(100)->get(),
        ]);
    }

    public function show(InventoryTransaction $transaction): Response
    {
        $transaction->load([
            'item',
            'batch',
            'epc',
            'fromWarehouse',
            'toWarehouse',
            'createdBy',
            'reversalOf',
        ]);

        // Get reversed transaction if this is a reversal
        $reversedTransaction = null;
        if ($transaction->is_reversal && $transaction->reversal_of) {
            $reversedTransaction = InventoryTransaction::with(['item', 'fromWarehouse', 'toWarehouse'])
                ->find($transaction->reversal_of);
        }

        // Check if this transaction has been reversed
        $reversalTransaction = InventoryTransaction::where('reversal_of', $transaction->id)->first();

        return Inertia::render('Inventory/Transactions/Show', [
            'transaction' => $transaction,
            'reversedTransaction' => $reversedTransaction,
            'reversalTransaction' => $reversalTransaction,
        ]);
    }

    public function reverse(Request $request, InventoryTransaction $transaction): RedirectResponse
    {
        // Check if already reversed
        if ($transaction->is_reversal) {
            return back()->with('error', __('Cannot reverse a reversal transaction'));
        }

        $existingReversal = InventoryTransaction::where('reversal_of', $transaction->id)->exists();
        if ($existingReversal) {
            return back()->with('error', __('Transaction has already been reversed'));
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $this->transactionService->createReversal($transaction, $validated['reason']);

        return back()->with('success', __('Transaction reversed successfully'));
    }
}
