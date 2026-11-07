<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Item $item,
        public float $currentStock,
        public float $reorderPoint
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Low Stock Alert',
            'message' => "Item {$this->item->name} is running low. Current stock: {$this->currentStock}, Reorder point: {$this->reorderPoint}",
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'sku' => $this->item->sku,
            'current_stock' => $this->currentStock,
            'reorder_point' => $this->reorderPoint,
            'action_url' => route('items.items.show', $this->item),
        ];
    }
}
