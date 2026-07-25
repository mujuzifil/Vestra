<?php

namespace Database\Factories;

use App\Enums\PaymentUploadStatus;
use App\Models\Distributor;
use App\Models\PaymentUpload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentUpload>
 */
class PaymentUploadFactory extends Factory
{
    protected $model = PaymentUpload::class;

    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'amount' => fake()->randomFloat(2, 10000, 500000),
            'currency' => 'UGX',
            'reference_number' => strtoupper(fake()->bothify('PAY-########')),
            'file_path' => 'payment_uploads/' . fake()->uuid() . '.pdf',
            'notes' => fake()->optional(0.5)->sentence(),
            'status' => PaymentUploadStatus::UPLOADED->value,
        ];
    }
}
