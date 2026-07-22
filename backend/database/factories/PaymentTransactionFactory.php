<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method' => 'card',
            'provider' => 'flutterwave',
            'transaction_reference' => 'VST-PAY-' . strtoupper(fake()->bothify('************')),
            'provider_reference' => null,
            'amount' => fake()->randomFloat(2, 20, 500),
            'currency' => 'UGX',
            'status' => 'pending',
            'response_data' => null,
            'paid_at' => null,
        ];
    }
}
