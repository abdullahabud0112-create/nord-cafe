<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('orders')];
    }

    public function broadcastAs(): string
    {
        return 'status-updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'status_label' => $this->order->status_label,
            'payment_status' => $this->order->payment_status,
        ];
    }
}
