<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image,
            'gallery' => $this->gallery,
            'content_blocks' => $this->content_blocks,
            'status' => $this->status?->value,
            'status_label' => $this->statusLabel(),
            'visibility' => $this->visibility?->value,
            'is_featured' => $this->is_featured,
            'reading_time_minutes' => $this->estimatedReadingTime(),
            'published_at' => $this->published_at?->toIso8601String(),
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'canonical_url' => $this->canonical_url,
            'view_count' => $this->view_count,
            'author' => $this->whenLoaded('author', fn () => new BlogAuthorResource($this->author)),
            'categories' => $this->whenLoaded('categories', fn () => BlogCategoryResource::collection($this->categories)),
            'tags' => $this->whenLoaded('tags', fn () => BlogTagResource::collection($this->tags)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
