<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\AdminNotificationService;
use App\Services\NotificationService;

class OrderObserver
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly AdminNotificationService $adminNotificationService,
    ) {}

    public function created(Order $order): void
    {
        // Notify admin of new order
        $this->adminNotificationService->newOrder(
            $order->invoice_number,
            $order->user->name,
            (float) $order->total_amount
        );
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $newStatus = OrderStatus::from($order->status);

        match ($newStatus) {
            OrderStatus::PENDING => $this->notificationService->sendOrderConfirmation($order),
            OrderStatus::PAID => $this->notificationService->sendPaymentConfirmation($order),
            OrderStatus::SHIPPED => $this->notificationService->sendShippingNotification($order),
            OrderStatus::DELIVERED => $this->notificationService->sendDeliveryNotification($order),
            default => null,
        };
    }
}
