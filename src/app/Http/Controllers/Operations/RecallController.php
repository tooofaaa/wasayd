<?php

namespace App\Http\Controllers\Operations;

use App\Enums\RecallClass;
use App\Enums\RecallStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecallRequest;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Recall;
use App\Models\RecallLine;
use App\Services\RecallService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecallController extends Controller
{
    public function __construct(
        private RecallService $recallService
    ) {}

    public function index(Request $request): Response
    {
        $query = Recall::query()
            ->with(['item', 'initiatedBy'])
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('recall_class')) {
            $query->where('recall_class', $request->recall_class);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recall_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $recalls = $query->latest()->paginate(15);

        return Inertia::render('Recalls/Index', [
            'recalls' => $recalls,
            'filters' => $request->only(['status', 'recall_class', 'search']),
            'statuses' => RecallStatus::cases(),
            'recallClasses' => RecallClass::cases(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Recalls/Create', [
            'items' => Item::where('company_id', auth()->user()->company_id)
                ->select('id', 'sku', 'name')->get(),
            'batches' => Batch::where('company_id', auth()->user()->company_id)
                ->select('id', 'batch_number', 'item_id')->get(),
            'recallClasses' => RecallClass::cases(),
        ]);
    }

    public function store(StoreRecallRequest $request): RedirectResponse
    {
        $recall = $this->recallService->initiateRecall($request->validated());

        return redirect()->route('operations.recalls.show', $recall)
            ->with('success', 'Recall initiated successfully');
    }

    public function show(Recall $recall): Response
    {
        $recall->load([
            'item',
            'lines.warehouse',
            'lines.batch',
            'initiatedBy'
        ]);

        return Inertia::render('Recalls/Show', [
            'recall' => $recall,
        ]);
    }

    public function quarantineLine(Recall $recall, RecallLine $line): RedirectResponse
    {
        $this->recallService->quarantineRecallLine($line->id);

        return redirect()->route('operations.recalls.show', $recall)
            ->with('success', 'Recall line quarantined successfully');
    }

    public function recoverLine(Recall $recall, RecallLine $line, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        $this->recallService->recoverRecallLine($line->id, $validated['quantity']);

        return redirect()->route('operations.recalls.show', $recall)
            ->with('success', 'Recall line recovered successfully');
    }

    public function complete(Recall $recall): RedirectResponse
    {
        $this->recallService->completeRecall($recall->id);

        return redirect()->route('operations.recalls.show', $recall)
            ->with('success', 'Recall completed successfully');
    }
}
