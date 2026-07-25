<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUserId = $request->user()?->id;

        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ], ['name' => 'Anonymous']),
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'image' => $this->product->images[0]?->image ?? null,
            ]),
            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'pros' => $this->pros ?? [],
            'cons' => $this->cons ?? [],
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => $image->url(),
                'sort_order' => $image->sort_order,
            ]), []),
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_pinned' => $this->is_pinned,
            'is_hidden' => $this->is_hidden,
            'helpful_count' => $this->helpful_count ?? 0,
            'user_vote' => $this->when($authUserId !== null, fn () => $this->getUserVote($authUserId)),
            'reported_count' => $this->reported_count ?? 0,
            'user_reported' => $this->when($authUserId !== null, fn () => $this->userReported($authUserId)),
            'admin_reply' => $this->when($this->admin_reply, fn () => [
                'content' => $this->admin_reply,
                'replied_at' => $this->admin_reply_at?->toDateTimeString(),
                'replied_by' => $this->whenLoaded('adminReplier', fn () => $this->adminReplier->name),
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

    protected function getUserVote(?int $userId): ?bool
    {
        if (! $userId || ! $this->relationLoaded('helpfulVotes')) {
            return null;
        }

        $vote = $this->helpfulVotes->firstWhere('user_id', $userId);

        return $vote ? $vote->is_helpful : null;
    }

    protected function userReported(?int $userId): bool
    {
        if (! $userId || ! $this->relationLoaded('reports')) {
            return false;
        }

        return $this->reports->contains('user_id', $userId);
    }
}
