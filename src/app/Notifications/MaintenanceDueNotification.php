<?php

namespace App\Notifications;

use App\Models\AssetMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public AssetMaintenance $maintenance,
        public bool $isOverdue = false
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $daysUntilDue = now()->diffInDays($this->maintenance->scheduled_date, false);

        return [
            'title' => $this->isOverdue ? 'Overdue Maintenance' : 'Maintenance Due Soon',
            'message' => sprintf(
                'Maintenance for %s (%s) is %s.',
                $this->maintenance->asset->name,
                $this->maintenance->asset->asset_code,
                $this->isOverdue ? 'overdue' : "due in {$daysUntilDue} days"
            ),
            'maintenance_id' => $this->maintenance->id,
            'asset_id' => $this->maintenance->asset_id,
            'asset_name' => $this->maintenance->asset->name,
            'asset_code' => $this->maintenance->asset->asset_code,
            'maintenance_type' => $this->maintenance->maintenance_type,
            'scheduled_date' => $this->maintenance->scheduled_date->format('Y-m-d'),
            'is_overdue' => $this->isOverdue,
            'action_url' => route('assets.show', $this->maintenance->asset_id),
        ];
    }
}
