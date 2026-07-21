<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendOrderConfirmation(Order $order): void
    {
        try {
            Mail::to($order->user->email)->send(new \App\Mail\OrderConfirmationMail($order));
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email: ' . $e->getMessage());
        }
    }

    public function sendPaymentConfirmation(Order $order): void
    {
        try {
            Mail::to($order->user->email)->send(new \App\Mail\PaymentConfirmationMail($order));
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email: ' . $e->getMessage());
        }
    }

    public function sendShippingNotification(Order $order): void
    {
        try {
            Mail::to($order->user->email)->send(new \App\Mail\ShippingNotificationMail($order));
        } catch (\Exception $e) {
            Log::error('Failed to send shipping notification email: ' . $e->getMessage());
        }
    }

    public function sendDeliveryNotification(Order $order): void
    {
        try {
            Mail::to($order->user->email)->send(new \App\Mail\DeliveryNotificationMail($order));
        } catch (\Exception $e) {
            Log::error('Failed to send delivery notification email: ' . $e->getMessage());
        }
    }
}
