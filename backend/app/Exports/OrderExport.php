<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Order::query()->with('user');
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Customer',
            'Email',
            'Total Amount',
            'Status',
            'Payment Status',
            'Payment Method',
            'Created At',
        ];
    }

    public function map($order): array
    {
        return [
            $order->invoice_number,
            $order->user?->name ?? 'Guest',
            $order->user?->email ?? '',
            $order->total_amount,
            $order->status,
            $order->payment_status,
            $order->payment_method,
            $order->created_at->toDateTimeString(),
        ];
    }
}
