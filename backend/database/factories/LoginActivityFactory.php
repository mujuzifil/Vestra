<?php

namespace Database\Factories;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginActivity>
 */
class LoginActivityFactory extends Factory
{
    protected $model = LoginActivity::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'successful' => true,
            'failed_reason' => null,
            'device' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'os' => fake()->randomElement(['Windows', 'macOS', 'Linux', 'Android', 'iOS']),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'location' => fake()->city().', '.fake()->country(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'successful' => false,
            'failed_reason' => fake()->randomElement(['invalid_credentials', 'account_locked', 'unauthorized']),
        ]);
    }
}
