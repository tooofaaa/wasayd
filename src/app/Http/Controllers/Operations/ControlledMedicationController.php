<?php

namespace App\Http\Controllers\Operations;

use App\Enums\ControlledAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DispenseControlledRequest;
use App\Http\Requests\ReconcileControlledRequest;
use App\Http\Requests\WasteControlledRequest;
use App\Models\Batch;
use App\Models\ControlledMedicationLog;
use App\Models\Item;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ControlledMedicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ControlledMedicationController extends Controller
{
    public function __construct(
        private ControlledMedicationService $controlledService
    ) {}

    public function index(Request $request): Response
    {
        $query = ControlledMedicationLog::query()
            ->with(['item', 'batch', 'warehouse', 'dispensedBy', 'witnessedBy'])
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_id', 'like', "%{$search}%")
                    ->orWhere('prescription_number', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('action_at')->paginate(15);

        return Inertia::render('ControlledMedications/Index', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'item_id', 'search']),
            'actions' => ControlledAction::cases(),
            'items' => Item::where('company_id', auth()->user()->company_id)
                ->where('item_type', 'narcotic_controlled')
                ->select('id', 'sku', 'name')->get(),
        ]);
    }

    public function showDispenseForm(): Response
    {
        return Inertia::render('ControlledMedications/Dispense', [
            'items' => Item::where('company_id', auth()->user()->company_id)
                ->where('item_type', 'narcotic_controlled')
                ->select('id', 'sku', 'name')->get(),
            'batches' => Batch::where('company_id', auth()->user()->company_id)
                ->select('id', 'batch_number', 'item_id')->get(),
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'users' => User::where('company_id', auth()->user()->company_id)
                ->select('id', 'name', 'email')->get(),
        ]);
    }

    public function dispense(DispenseControlledRequest $request): RedirectResponse
    {
        $this->controlledService->dispense($request->validated());

        return redirect()->route('operations.controlled-medications.index')
            ->with('success', 'Controlled medication dispensed successfully');
    }

    public function showWasteForm(): Response
    {
        return Inertia::render('ControlledMedications/Waste', [
            'items' => Item::where('company_id', auth()->user()->company_id)
                ->where('item_type', 'narcotic_controlled')
                ->select('id', 'sku', 'name')->get(),
            'batches' => Batch::where('company_id', auth()->user()->company_id)
                ->select('id', 'batch_number', 'item_id')->get(),
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'users' => User::where('company_id', auth()->user()->company_id)
                ->select('id', 'name', 'email')->get(),
        ]);
    }

    public function waste(WasteControlledRequest $request): RedirectResponse
    {
        $this->controlledService->waste($request->validated());

        return redirect()->route('operations.controlled-medications.index')
            ->with('success', 'Controlled medication waste recorded successfully');
    }

    public function showReconcileForm(): Response
    {
        return Inertia::render('ControlledMedications/Reconcile', [
            'items' => Item::where('company_id', auth()->user()->company_id)
                ->where('item_type', 'narcotic_controlled')
                ->select('id', 'sku', 'name')->get(),
            'batches' => Batch::where('company_id', auth()->user()->company_id)
                ->select('id', 'batch_number', 'item_id')->get(),
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->select('id', 'name')->get(),
            'users' => User::where('company_id', auth()->user()->company_id)
                ->select('id', 'name', 'email')->get(),
        ]);
    }

    public function reconcile(ReconcileControlledRequest $request): RedirectResponse
    {
        $this->controlledService->reconcile($request->validated());

        return redirect()->route('operations.controlled-medications.index')
            ->with('success', 'Controlled medication reconciliation completed successfully');
    }
}
