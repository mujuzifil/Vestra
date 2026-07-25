<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number' => strtoupper(fake()->unique()->bothify('PO-######')),
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'status' => PurchaseOrderStatus::ORDERED->value,
            'total' => fake()->randomFloat(2, 100000, 5000000),
            'notes' => fake()->optional()->sentence(),
            'ordered_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'expected_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
        ];
    }
}
