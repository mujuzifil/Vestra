<?php

namespace Database\Factories;

use App\Models\ApiRequestLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiRequestLog>
 */
class ApiRequestLogFactory extends Factory
{
    protected $model = ApiRequestLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'method' => fake()->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            'path' => '/api/v1/' . fake()->word(),
            'status_code' => fake()->randomElement([200, 201, 400, 401, 404, 422, 500]),
            'duration_ms' => fake()->numberBetween(20, 2000),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
