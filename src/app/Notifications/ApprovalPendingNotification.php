<?php

namespace App\Notifications;

use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Approval $approval
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Approval Pending',
            'message' => "You have a pending approval request for {$this->approval->approvable_type}.",
            'approval_id' => $this->approval->id,
            'approvable_type' => class_basename($this->approval->approvable_type),
            'approvable_id' => $this->approval->approvable_id,
            'requested_by' => $this->approval->requestedBy?->name ?? 'Unknown',
            'requested_at' => $this->approval->created_at->format('Y-m-d H:i'),
            'action_url' => route('inventory.approvals.index'),
        ];
    }
}
