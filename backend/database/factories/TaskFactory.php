<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(TaskStatus::cases());

        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $status->value,
            'priority' => $this->faker->randomElement(TaskPriority::cases())->value,
            'assignee_id' => User::factory(),
            'created_by_id' => User::factory(),
            'due_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+30 days'),
            'completed_at' => $status === TaskStatus::COMPLETED ? now() : null,
            'internal_notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->subDays($this->faker->numberBetween(1, 14)),
            'completed_at' => null,
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::COMPLETED->value,
            'completed_at' => now(),
        ]);
    }
}
