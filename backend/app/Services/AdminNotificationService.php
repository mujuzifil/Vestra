<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminNotificationService
{
    private string $adminEmail;

    public function __construct()
    {
        $this->adminEmail = config('mail.admin_address', 'vestradetergent@gmail.com');
    }

    public function notify(string $subject, string $content, array $data = []): void
    {
        try {
            Mail::to($this->adminEmail)->send(new \App\Mail\AdminNotificationMail($subject, $content, $data));
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    public function newCustomer(string $name, string $email): void
    {
        $this->notify(
            'New Customer Registration: ' . $name,
            "A new customer has registered on VESTRA.",
            ['Customer Name' => $name, 'Email' => $email]
        );
    }

    public function newOrder(string $invoiceNumber, string $customer, float $total): void
    {
        $this->notify(
            'New Order: ' . $invoiceNumber,
            "A new order has been placed.",
            ['Invoice' => $invoiceNumber, 'Customer' => $customer, 'Total' => 'UGX ' . number_format($total, 2)]
        );
    }

    public function lowStock(string $productName, string $sku, int $quantity): void
    {
        $this->notify(
            'Low Stock Alert: ' . $productName,
            "A product is running low on stock.",
            ['Product' => $productName, 'SKU' => $sku, 'Stock' => $quantity]
        );
    }

    public function newContactMessage(string $name, string $subject): void
    {
        $this->notify(
            'New Contact Message from ' . $name,
            "A new contact message has been received.",
            ['Name' => $name, 'Subject' => $subject]
        );
    }

    public function newFeedback(string $category, string $subject): void
    {
        $this->notify(
            'New Feedback: ' . $category,
            "New customer feedback has been submitted.",
            ['Category' => $category, 'Subject' => $subject]
        );
    }
}
