<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Product::query()->with('category');
    }

    public function headings(): array
    {
        return [
            'Name',
            'SKU',
            'Category',
            'Price',
            'Stock Quantity',
            'Status',
            'Featured',
            'Created At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->sku,
            $product->category?->name ?? '',
            $product->price,
            $product->stock_quantity,
            $product->status,
            $product->featured ? 'Yes' : 'No',
            $product->created_at->toDateTimeString(),
        ];
    }
}
