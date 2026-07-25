<?php

namespace App\Events\Notification;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPacked
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order) {}
}
