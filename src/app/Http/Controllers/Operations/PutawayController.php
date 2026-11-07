<?php

namespace App\Http\Controllers\Operations;

use App\Enums\PutawayStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePutawayLineRequest;
use App\Models\Putaway;
use App\Models\PutawayLine;
use App\Services\PutawayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PutawayController extends Controller
{
    public function __construct(
        private PutawayService $putawayService
    ) {}

    public function index(Request $request): Response
    {
        $query = Putaway::query()
            ->with(['receiving', 'warehouse', 'assignedTo'])
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('putaway_number', 'like', "%{$search}%");
        }

        $putaways = $query->latest()->paginate(15);

        return Inertia::render('Putaways/Index', [
            'putaways' => $putaways,
            'filters' => $request->only(['status', 'search']),
            'statuses' => PutawayStatus::cases(),
        ]);
    }

    public function show(Putaway $putaway): Response
    {
        $putaway->load([
            'receiving',
            'warehouse',
            'lines.item',
            'lines.batch',
            'lines.toZone',
            'lines.toBin',
            'assignedTo',
            'createdBy'
        ]);

        // Get suggested bins for each line
        $putaway->lines->each(function ($line) {
            if (!$line->to_bin_id) {
                $line->suggested_bin = $this->putawayService->suggestLocation(
                    $line->item_id,
                    $line->putaway->warehouse_id
                );
            }
        });

        return Inertia::render('Putaways/Show', [
            'putaway' => $putaway,
        ]);
    }

    public function confirmLine(Putaway $putaway, PutawayLine $line, StorePutawayLineRequest $request): RedirectResponse
    {
        $this->putawayService->confirmPutaway($line, $request->validated()['to_bin_id']);

        return redirect()->route('operations.putaways.show', $putaway)
            ->with('success', 'Putaway line confirmed successfully');
    }

    public function complete(Putaway $putaway): RedirectResponse
    {
        // Check all lines are placed
        $pendingLines = $putaway->lines()->where('status', '!=', 'placed')->count();

        if ($pendingLines > 0) {
            return redirect()->route('operations.putaways.show', $putaway)
                ->with('error', "Cannot complete putaway. {$pendingLines} line(s) are still pending.");
        }

        $putaway->update([
            'status' => PutawayStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('operations.putaways.index')
            ->with('success', 'Putaway completed successfully');
    }
}
