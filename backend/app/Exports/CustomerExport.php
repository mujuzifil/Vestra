<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return User::query()->where('is_admin', false)->withCount('orders');
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Orders Count',
            'Created At',
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->phone ?? '',
            $user->orders_count,
            $user->created_at->toDateTimeString(),
        ];
    }
}
