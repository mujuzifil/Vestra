<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('is_admin', false)->orWhereNull('is_admin')->get();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        Order::factory()
            ->count(30)
            ->create([
                'user_id' => fn () => $customers->random()->id,
            ])
            ->each(function (Order $order) use ($products) {
                $items = $products->random(rand(1, 3));
                $subtotal = 0;

                foreach ($items as $product) {
                    $quantity = rand(1, 3);
                    $lineTotal = round($product->price * $quantity, 2);
                    $subtotal += $lineTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'unit_price' => $product->price,
                        'quantity' => $quantity,
                        'line_total' => $lineTotal,
                    ]);
                }

                $shipping = $order->shipping_cost ?: 10;
                $tax = round($subtotal * 0.18, 2);
                $order->update([
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping,
                    'tax_amount' => $tax,
                    'total_amount' => round($subtotal + $shipping + $tax, 2),
                ]);

                // Seed status history for non-pending orders.
                if ($order->status !== OrderStatus::PENDING->value) {
                    $order->statusHistory()->create([
                        'status' => $order->status,
                        'notes' => 'Order status set by demo seeder.',
                        'changed_by' => null,
                        'created_at' => $order->created_at,
                    ]);
                }
            });
    }
}
