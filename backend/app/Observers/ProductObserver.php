<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\AdminNotificationService;

class ProductObserver
{
    public function __construct(private readonly AdminNotificationService $service) {}

    public function updated(Product $product): void
    {
        if ($product->wasChanged('stock_quantity')) {
            $newStock = $product->stock_quantity;

            // Auto-update status based on stock
            if ($newStock <= 0) {
                $product->update(['status' => 'out_of_stock']);
            } elseif ($newStock > 0 && $product->status === 'out_of_stock') {
                $product->update(['status' => 'active']);
            }

            // Send low stock alert
            if ($newStock <= 10 && $newStock > 0) {
                $this->service->lowStock($product->name, $product->sku, $newStock);
            }
        }
    }
}
