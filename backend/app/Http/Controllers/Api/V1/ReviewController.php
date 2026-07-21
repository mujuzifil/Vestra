<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReviewRequest;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ->with('user')
            ->latest()
            ->paginate($request->get('per_page', 10));

        return $this->successResponse([
            'reviews' => ReviewResource::collection($reviews),
            'average_rating' => $product->averageRating(),
            'review_count' => $product->reviewCount(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // Check if user has purchased this product
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

        // Check if user already reviewed this product
        $existing = Review::where('user_id', $user->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'product_id' => ['You have already reviewed this product.'],
            ]);
        }

        $review = new Review();
        $review->forceFill([
            'user_id' => $user->id,
            'product_id' => $data['product_id'],
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'status' => 'pending',
        ])->save();

        return $this->successResponse(
            new ReviewResource($review->load('user')),
            'Review submitted successfully. It will be visible after moderation.',
            201
        );
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $data = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'sometimes|nullable|string|max:255',
            'comment' => 'sometimes|nullable|string|max:1000',
        ]);

        $review->update($data);
        $review->forceFill(['status' => 'pending'])->save();

        return $this->successResponse(
            new ReviewResource($review->fresh()->load('user')),
            'Review updated successfully.'
        );
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return $this->successResponse(null, 'Review deleted successfully.');
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Review::class);

        $reviews = Review::with(['user', 'product'])
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

        $review->forceFill(['status' => $data['status']])->save();

        return $this->successResponse(
            new ReviewResource($review->fresh()->load('user')),
            'Review status updated successfully.'
        );
    }
}
