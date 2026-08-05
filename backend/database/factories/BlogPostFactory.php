<?php

namespace Database\Factories;

use App\Enums\BlogPostStatus;
use App\Enums\BlogPostVisibility;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        return [
            'author_id' => null,
            'title' => fake()->unique()->sentence(4),
            'slug' => fake()->unique()->slug(4),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'status' => BlogPostStatus::DRAFT->value,
            'visibility' => BlogPostVisibility::PUBLIC->value,
            'is_featured' => false,
            'show_on_homepage' => false,
            'is_pinned' => false,
            'allow_comments' => true,
            'reading_time_minutes' => fake()->numberBetween(2, 10),
            'published_at' => null,
            'scheduled_at' => null,
            'view_count' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => BlogPostStatus::PUBLISHED->value,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => BlogPostStatus::SCHEDULED->value,
            'scheduled_at' => now()->addDays(fake()->numberBetween(1, 10)),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => BlogPostStatus::ARCHIVED->value,
        ]);
    }
}
