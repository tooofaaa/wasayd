<?php

namespace App\Notifications;

use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpiryAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Batch $batch,
        public int $daysUntilExpiry
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $urgency = match (true) {
            $this->daysUntilExpiry <= 7 => 'critical',
            $this->daysUntilExpiry <= 15 => 'urgent',
            $this->daysUntilExpiry <= 30 => 'warning',
            default => 'normal',
        };

        return [
            'title' => 'Expiry Alert',
            'message' => "Batch {$this->batch->batch_number} for {$this->batch->item->name} expires in {$this->daysUntilExpiry} days.",
            'batch_id' => $this->batch->id,
            'batch_number' => $this->batch->batch_number,
            'item_name' => $this->batch->item->name,
            'expiry_date' => $this->batch->expiry_date->format('Y-m-d'),
            'days_until_expiry' => $this->daysUntilExpiry,
            'urgency' => $urgency,
            'action_url' => route('batch.batches.show', $this->batch),
        ];
    }
}
