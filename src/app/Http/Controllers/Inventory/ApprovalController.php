<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\ApprovalStep;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function __construct(
        private ApprovalService $approvalService
    ) {}

    public function index(Request $request): Response
    {
        $userId = auth()->id();

        // Get pending approvals assigned to current user
        $query = ApprovalStep::query()
            ->with(['approval.approvable', 'approval.requestedBy'])
            ->where('company_id', auth()->user()->company_id)
            ->where('status', ApprovalStatus::Pending)
            ->where(function ($q) use ($userId) {
                $q->where('approver_id', $userId)
                    ->orWhereNull('approver_id');
            });

        if ($request->filled('type')) {
            $query->whereHas('approval', function ($q) use ($request) {
                $q->where('approval_type', $request->type);
            });
        }

        $pendingApprovals = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Inventory/Approvals/Index', [
            'pendingApprovals' => $pendingApprovals,
            'filters' => $request->only(['type']),
        ]);
    }

    public function approve(Request $request, Approval $approval): RedirectResponse
    {
        $validated = $request->validate([
            'comments' => 'nullable|string',
        ]);

        // Find the pending step for this user
        $step = $approval->steps()
            ->where('status', ApprovalStatus::Pending)
            ->where(function ($q) {
                $q->where('approver_id', auth()->id())
                    ->orWhereNull('approver_id');
            })
            ->orderBy('step_number')
            ->first();

        if (!$step) {
            return back()->with('error', __('No pending approval step found'));
        }

        try {
            $this->approvalService->approveStep(
                $step->id,
                auth()->id(),
                $validated['comments'] ?? null
            );

            return back()->with('success', __('Approval granted successfully'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Approval $approval): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        // Find the pending step for this user
        $step = $approval->steps()
            ->where('status', ApprovalStatus::Pending)
            ->where(function ($q) {
                $q->where('approver_id', auth()->id())
                    ->orWhereNull('approver_id');
            })
            ->orderBy('step_number')
            ->first();

        if (!$step) {
            return back()->with('error', __('No pending approval step found'));
        }

        try {
            $this->approvalService->rejectStep(
                $step->id,
                auth()->id(),
                $validated['reason']
            );

            return back()->with('success', __('Approval rejected'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
