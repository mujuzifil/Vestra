<?php

namespace App\Services;

use App\Enums\DistributorChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DistributorOrderService
{
    public function __construct(
        private readonly DistributorPriceService $priceService,
        private readonly CreditAccountService $creditService,
        private readonly PaymentService $paymentService,
    ) {}

    public function createOrder(User $user, Distributor $distributor, array $data): Order
    {
        return DB::transaction(function () use ($user, $distributor, $data) {
            $cart = $user->cart?->load('items.product');

            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Your cart is empty.'],
                ]);
            }

            $subtotal = 0;
            $orderItems = [];
            $productIds = [];

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                if ($product->status !== ProductStatus::ACTIVE) {
                    throw ValidationException::withMessages([
                        'stock' => ["{$product->name} is currently unavailable."],
                    ]);
                }

                if ($product->stock_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => ["Not enough stock for {$product->name}. Only {$product->stock_quantity} left."],
                    ]);
                }

                $lineTotal = round(
                    $this->priceService->resolveOrRetail($product, $cartItem->quantity, $distributor) * $cartItem->quantity,
                    2
                );
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $lineTotal / $cartItem->quantity,
                    'quantity' => $cartItem->quantity,
                    'line_total' => $lineTotal,
                ];

                $productIds[] = $product->id;
            }

            $taxRate = $this->taxRate();
            $taxAmount = round($subtotal * $taxRate, 2);
            $totalAmount = round($subtotal + $taxAmount, 2);
            $paymentMethod = $data['payment_method'];
            $isCredit = $paymentMethod === PaymentMethod::CREDIT->value;

            $order = Order::create([
                'user_id' => $user->id,
                'distributor_id' => $distributor->id,
                'channel' => DistributorChannel::DISTRIBUTOR->value,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => OrderStatus::PENDING->value,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::PENDING->value,
                'stock_decremented' => false,
                'shipping_address' => array_merge(
                    $data['shipping_address'],
                    ['distributor_branch_id' => $data['distributor_branch_id'] ?? null]
                ),
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($orderItems);

            if ($isCredit) {
                $authorized = $this->creditService->authorize($distributor, $totalAmount, $order);

                if (! $authorized) {
                    throw ValidationException::withMessages([
                        'payment_method' => ['Insufficient available credit or credit account is not active.'],
                    ]);
                }

                $order->setAttribute('payment_required', true);
            } else {
                $paymentResult = $this->paymentService->initiate($order);
                if ($paymentResult['success']) {
                    $order->setAttribute('payment_url', $paymentResult['payment_link']);
                }
            }

            $cart->items()->delete();

            return $order->load('items');
        });
    }

    public function getDistributorOrders(Distributor $distributor)
    {
        return $distributor->orders()
            ->with('items')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getDistributorOrder(Distributor $distributor, int $orderId): ?Order
    {
        return $distributor->orders()
            ->with('items')
            ->find($orderId);
    }

    private function taxRate(): float
    {
        $setting = \App\Models\Setting::where('key', 'tax_rate')->first();

        return is_numeric($setting?->typedValue()) ? (float) $setting->typedValue() : 0.18;
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'VST-D';
        $date = Carbon::now()->format('Ymd');
        $sequence = Order::whereDate('created_at', Carbon::today())->count() + 1;
        $sequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }
}
