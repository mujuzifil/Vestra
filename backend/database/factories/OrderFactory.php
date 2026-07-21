<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 500);
        $shipping = fake()->randomFloat(2, 5, 30);
        $tax = round($subtotal * 0.18, 2);
        $total = round($subtotal + $shipping + $tax, 2);

        return [
            'user_id' => User::factory(),
            'invoice_number' => 'INV-' . strtoupper(fake()->bothify('??####')),
            'status' => fake()->randomElement(OrderStatus::cases())->value,
            'payment_method' => fake()->randomElement(PaymentMethod::cases())->value,
            'payment_status' => fake()->randomElement(PaymentStatus::cases())->value,
            'shipping_address' => [
                'full_name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'address_line' => fake()->streetAddress(),
                'city' => fake()->city(),
                'region' => fake()->state(),
            ],
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'notes' => fake()->optional(0.3)->sentence(),
            'courier' => fake()->optional(0.4)->company(),
            'tracking_number' => fake()->optional(0.3)->bothify('TRK-########'),
            'internal_notes' => fake()->optional(0.2)->sentence(),
            'created_at' => fake()->dateTimeBetween('-90 days', 'now'),
            'updated_at' => fake()->dateTimeBetween('-90 days', 'now'),
        ];
    }
}
