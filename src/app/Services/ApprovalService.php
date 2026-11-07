<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Models\Approval;
use App\Models\ApprovalStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(
        protected SodService $sodService
    ) {}

    /**
     * Submit an entity for approval
     */
    public function submitForApproval(Model $approvable, string $approvalType): Approval
    {
        return DB::transaction(function () use ($approvable, $approvalType) {
            // Create approval record
            $approval = Approval::create([
                'company_id' => $approvable->company_id,
                'approvable_type' => get_class($approvable),
                'approvable_id' => $approvable->id,
                'approval_type' => $approvalType,
                'status' => ApprovalStatus::Pending,
                'requested_by' => auth()->id(),
            ]);

            // Get approval workflow steps from config
            $steps = $this->getApprovalSteps($approvalType);

            foreach ($steps as $order => $step) {
                ApprovalStep::create([
                    'company_id' => $approvable->company_id,
                    'approval_id' => $approval->id,
                    'step_order' => $order + 1,
                    'role' => $step['role'],
                    'user_id' => $step['user_id'] ?? null,
                    'status' => ApprovalStatus::Pending,
                ]);
            }

            return $approval;
        });
    }

    /**
     * Approve a step in the workflow
     */
    public function approveStep(int $stepId, int $userId, ?string $comments = null): ApprovalStep
    {
        return DB::transaction(function () use ($stepId, $userId, $comments) {
            $step = ApprovalStep::with('approval')->findOrFail($stepId);

            // Validate SoD
            $this->sodService->validateSod($step->approval->requested_by, $userId);

            $step->update([
                'status' => ApprovalStatus::Approved,
                'acted_by' => $userId,
                'acted_at' => now(),
                'comments' => $comments,
            ]);

            // Check if all steps are approved
            $approval = $step->approval;
            $pendingSteps = $approval->steps()->where('status', ApprovalStatus::Pending)->count();

            if ($pendingSteps === 0) {
                $approval->update([
                    'status' => ApprovalStatus::Approved,
                    'completed_at' => now(),
                ]);

                // Update approvable status
                if (method_exists($approval->approvable, 'onApprovalComplete')) {
                    $approval->approvable->onApprovalComplete();
                }
            }

            return $step->fresh();
        });
    }

    /**
     * Reject a step in the workflow
     */
    public function rejectStep(int $stepId, int $userId, string $reason): ApprovalStep
    {
        return DB::transaction(function () use ($stepId, $userId, $reason) {
            $step = ApprovalStep::with('approval')->findOrFail($stepId);

            // Validate SoD
            $this->sodService->validateSod($step->approval->requested_by, $userId);

            $step->update([
                'status' => ApprovalStatus::Rejected,
                'acted_by' => $userId,
                'acted_at' => now(),
                'comments' => $reason,
            ]);

            // Reject the entire approval
            $step->approval->update([
                'status' => ApprovalStatus::Rejected,
                'completed_at' => now(),
            ]);

            return $step->fresh();
        });
    }

    /**
     * Get approval workflow steps for a type
     */
    protected function getApprovalSteps(string $approvalType): array
    {
        // In a real application, this would come from config or database
        // For now, return a simple two-step approval
        return [
            ['role' => 'supervisor'],
            ['role' => 'manager'],
        ];
    }
}
