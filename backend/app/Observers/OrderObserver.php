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
        // Send customer order confirmation immediately on creation.
        $this->notificationService->sendOrderConfirmation($order);

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
            OrderStatus::PAID => $this->notificationService->sendPaymentConfirmation($order),
            OrderStatus::PROCESSING => $this->notificationService->sendProcessingNotification($order),
            OrderStatus::PACKED => $this->notificationService->sendPackedNotification($order),
            OrderStatus::SHIPPED => $this->notificationService->sendShippingNotification($order),
            OrderStatus::DELIVERED => $this->notificationService->sendDeliveryNotification($order),
            OrderStatus::CANCELLED => $this->notificationService->sendCancelledNotification($order),
            OrderStatus::REFUNDED => $this->notificationService->sendRefundedNotification($order),
            default => null,
        };
    }
}
