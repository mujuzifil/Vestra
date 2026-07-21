<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function findById(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?Order
    {
        return Order::with('items')->where('invoice_number', $invoiceNumber)->first();
    }
}
