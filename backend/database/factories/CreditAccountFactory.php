<?php

namespace Database\Factories;

use App\Models\CreditAccount;
use App\Models\Distributor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditAccount>
 */
class CreditAccountFactory extends Factory
{
    protected $model = CreditAccount::class;

    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'limit' => fake()->randomFloat(2, 100000, 1000000),
            'balance' => 0,
            'authorized_amount' => 0,
            'status' => 'active',
        ];
    }
}
