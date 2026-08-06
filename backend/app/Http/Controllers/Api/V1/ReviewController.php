<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReviewRequest;
use App\Http\Requests\Api\V1\UpdateReviewRequest;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Product;
use App\Events\Notification\ReviewApproved;
use App\Events\Notification\ReviewReplied;
use App\Models\Review;
use App\Models\ReviewHelpfulVote;
use App\Models\ReviewImage;
use App\Models\ReviewReport;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->first();

        if (! $product) {
            return $this->errorResponse('Product not found.', 404);
        }

        $reviews = Review::where('product_id', $product->id)
            ->where('status', 'approved')
            ->where('is_hidden', false)
            ->with(['user', 'images', 'adminReplier'])
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate($request->get('per_page', 10));

        return $this->successResponse([
            'reviews' => ReviewResource::collection($reviews),
            'average_rating' => $product->averageRating(),
            'review_count' => $product->reviewCount(),
            'rating_distribution' => $this->ratingDistribution($product->id),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function myReviews(Request $request): JsonResponse
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with(['product', 'images'])
            ->latest()
            ->paginate($request->get('per_page', 10));

        return $this->successResponse(ReviewResource::collection($reviews));
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $hasPurchased = $user->orders()
            ->whereHas('items', function ($query) use ($data) {
                $query->where('product_id', $data['product_id']);
            })
            ->whereIn('status', ['paid', 'processing', 'packed', 'shipped', 'delivered'])
            ->exists();

        if (! $hasPurchased) {
            throw ValidationException::withMessages([
                'product_id' => ['You can only review products you have purchased.'],
            ]);
        }

        $existing = Review::where('user_id', $user->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'product_id' => ['You have already reviewed this product.'],
            ]);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $data['product_id'],
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'pros' => $data['pros'] ?? [],
            'cons' => $data['cons'] ?? [],
            'status' => 'pending',
        ]);

        $this->storeImages($request, $review);

        return $this->successResponse(
            new ReviewResource($review->load(['user', 'images'])),
            'Review submitted successfully. It will be visible after moderation.',
            201
        );
    }

    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $data = $request->validated();

        $review->update([
            'rating' => $data['rating'] ?? $review->rating,
            'title' => $data['title'] ?? $review->title,
            'comment' => $data['comment'] ?? $review->comment,
            'pros' => $data['pros'] ?? $review->pros,
            'cons' => $data['cons'] ?? $review->cons,
            'status' => 'pending',
        ]);

        $this->storeImages($request, $review);

        return $this->successResponse(
            new ReviewResource($review->fresh()->load(['user', 'images'])),
            'Review updated successfully.'
        );
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return $this->successResponse(null, 'Review deleted successfully.');
    }

    public function helpful(Request $request, Review $review): JsonResponse
    {
        $this->authorize('vote', $review);

        $data = $request->validate([
            'is_helpful' => 'required|boolean',
        ]);

        $vote = ReviewHelpfulVote::updateOrCreate(
            ['review_id' => $review->id, 'user_id' => $request->user()->id],
            ['is_helpful' => $data['is_helpful']]
        );

        $helpfulCount = ReviewHelpfulVote::where('review_id', $review->id)
            ->where('is_helpful', true)
            ->count();

        $review->update(['helpful_count' => $helpfulCount]);

        return $this->successResponse([
            'helpful_count' => $helpfulCount,
            'user_vote' => $vote->is_helpful,
        ]);
    }

    public function report(Request $request, Review $review): JsonResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        ReviewReport::create([
            'review_id' => $review->id,
            'user_id' => $request->user()->id,
            'reason' => $data['reason'],
            'details' => $data['details'],
            'status' => 'pending',
        ]);

        $review->increment('reported_count');

        return $this->successResponse(null, 'Review reported successfully.');
    }

    public function reply(Request $request, Review $review): JsonResponse
    {
        $this->authorize('moderate', $review);

        $data = $request->validate([
            'admin_reply' => 'required|string|max:2000',
        ]);

        $review->update([
            'admin_reply' => $data['admin_reply'],
            'admin_reply_at' => now(),
            'admin_reply_by' => $request->user()->id,
        ]);

        ReviewReplied::dispatch($review->fresh()->load(['user', 'product']));

        return $this->successResponse(
            new ReviewResource($review->fresh()->load(['user', 'images', 'adminReplier'])),
            'Reply added successfully.'
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Review::class);

        $reviews = Review::with(['user', 'product', 'images', 'reports'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->successResponse(ReviewResource::collection($reviews));
    }

    public function updateStatus(Request $request, Review $review): JsonResponse
    {
        $this->authorize('moderate', $review);

        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $wasApproved = $review->status !== 'approved' && $data['status'] === 'approved';

        $review->forceFill(['status' => $data['status']])->save();

        if ($wasApproved) {
            ReviewApproved::dispatch($review->fresh()->load(['user', 'product']));
        }

        return $this->successResponse(
            new ReviewResource($review->fresh()->load(['user', 'images'])),
            'Review status updated successfully.'
        );
    }

    protected function ratingDistribution(int $productId): array
    {
        $distribution = Review::where('product_id', $productId)
            ->where('status', 'approved')
            ->where('is_hidden', false)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $result = [];
        for ($i = 5; $i >= 1; $i--) {
            $result[] = ['rating' => $i, 'count' => $distribution[$i] ?? 0];
        }

        return $result;
    }

    protected function storeImages(Request $request, Review $review): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $files = $request->file('images');
        if (! is_array($files)) {
            $files = [$files];
        }

        $sortOrder = $review->images()->max('sort_order') ?? 0;

        foreach ($files as $file) {
            $path = $file->store('review-images', 'public');
            $sortOrder++;
            ReviewImage::create([
                'review_id' => $review->id,
                'path' => $path,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
