<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    private const CURRENCY = 'UGX';

    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly OrderStatusService $orderStatusService,
    ) {}

    public function initiate(Order $order): array
    {
        $reference = 'VST-PAY-' . strtoupper(Str::random(12));

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'payment_method' => $order->payment_method,
            'provider' => 'flutterwave',
            'transaction_reference' => $reference,
            'amount' => $order->total_amount,
            'currency' => self::CURRENCY,
            'status' => 'initiated',
        ]);

        $result = $this->gateway->initiate(
            (float) $order->total_amount,
            self::CURRENCY,
            $reference,
            [
                'order_id' => $order->id,
                'customer_email' => $order->user->email,
                'customer_name' => $order->user->name,
                'redirect_url' => config('app.frontend_url') . '/checkout/return',
            ]
        );

        if (! $result['success']) {
            $transaction->update(['status' => 'failed']);
            return $result;
        }

        $transaction->update(['status' => 'pending']);

        return [
            'success' => true,
            'payment_link' => $result['payment_link'],
            'transaction_reference' => $reference,
        ];
    }

    public function verifyAndProcess(string $reference): array
    {
        return DB::transaction(function () use ($reference) {
            $transaction = PaymentTransaction::where('transaction_reference', $reference)->lockForUpdate()->first();

            if (! $transaction) {
                return ['success' => false, 'message' => 'Transaction not found.'];
            }

            // Idempotency: already processed
            if ($transaction->status === 'success') {
                return ['success' => true, 'message' => 'Payment already confirmed.'];
            }

            $result = $this->gateway->verify($reference);

            if (! $result['success']) {
                $transaction->update([
                    'status' => 'failed',
                    'response_data' => $result,
                ]);
                return $result;
            }

            $order = Order::lockForUpdate()->with('items')->find($transaction->order_id);

            if (! $order) {
                return ['success' => false, 'message' => 'Order not found.'];
            }

            // Verify payment amount and currency match the order to prevent tampering.
            if (! $this->amountMatches($order, $result)) {
                $transaction->update([
                    'status' => 'failed',
                    'response_data' => $result,
                ]);

                return ['success' => false, 'message' => 'Payment amount does not match order.'];
            }

            $transaction->update([
                'status' => 'success',
                'provider_reference' => $result['provider_reference'],
                'paid_at' => $result['paid_at'],
                'response_data' => $result,
            ]);

            $order->update([
                'payment_status' => PaymentStatus::PAID->value,
            ]);

            // Decrement stock idempotently for digital payments.
            $this->decrementStock($order);

            // Update status to paid
            $this->orderStatusService->transition($order, OrderStatus::PAID, 'Payment confirmed via Flutterwave.');

            return ['success' => true, 'message' => 'Payment verified and order updated.'];
        });
    }

    public function handleCallback(array $payload): array
    {
        $result = $this->gateway->handleCallback($payload);

        if (! $result['success'] || empty($payload['tx_ref'])) {
            return $result;
        }

        return $this->verifyAndProcess($payload['tx_ref']);
    }

    private function amountMatches(Order $order, array $result): bool
    {
        $expectedAmount = round((float) $order->total_amount, 2);
        $actualAmount = round((float) ($result['amount'] ?? 0), 2);
        $currency = strtoupper((string) ($result['currency'] ?? self::CURRENCY));

        return $currency === self::CURRENCY && abs($expectedAmount - $actualAmount) < 0.01;
    }

    private function decrementStock(Order $order): void
    {
        if ($order->stock_decremented) {
            return;
        }

        $productIds = $order->items->pluck('product_id')->toArray();
        sort($productIds);

        $lockedProducts = Product::lockForUpdate()->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $product = $lockedProducts->get($item->product_id);
            if ($product) {
                $product->decrement('stock_quantity', $item->quantity);
            }
        }

        $order->update(['stock_decremented' => true]);
    }
}
