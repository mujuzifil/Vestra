<?php

namespace Database\Factories;

use App\Models\CustomerPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerPreference>
 */
class CustomerPreferenceFactory extends Factory
{
    protected $model = CustomerPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_preferences' => [],
            'account_preferences' => [],
        ];
    }
}
