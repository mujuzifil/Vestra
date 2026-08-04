<?php

namespace Database\Factories;

use App\Models\BlogAuthor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogAuthor>
 */
class BlogAuthorFactory extends Factory
{
    protected $model = BlogAuthor::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'slug' => fake()->unique()->slug(2),
            'email' => fake()->unique()->safeEmail(),
            'role' => fake()->randomElement(['Editor', 'Writer', 'Contributor']),
            'bio' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
