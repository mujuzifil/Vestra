<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BlogCategoryResource;
use App\Http\Resources\V1\BlogPostResource;
use App\Http\Resources\V1\BlogTagResource;
use App\Services\BlogPostService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly BlogPostService $service) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 12);
        $filters = [
            'category' => $request->input('category'),
            'tag' => $request->input('tag'),
            'search' => $request->input('search'),
            'sort' => $request->input('sort', 'newest'),
        ];

        $posts = $this->service->getPublishedPosts($filters, max(1, min($perPage, 100)));

        return $this->successResponse(
            BlogPostResource::collection($posts)->response()->getData(true)
        );
    }

    public function featured(): JsonResponse
    {
        $post = $this->service->getFeaturedPost();

        if (! $post) {
            return $this->successResponse(null);
        }

        return $this->successResponse(new BlogPostResource($post));
    }

    public function homepage(Request $request): JsonResponse
    {
        $limit = max(1, min($request->integer('limit', 6), 24));
        $posts = $this->service->getHomepagePosts($limit);

        return $this->successResponse(
            BlogPostResource::collection($posts)->resolve()
        );
    }

    public function show(string $slug): JsonResponse
    {
        try {
            $post = $this->service->getPostBySlug($slug);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->errorResponse('Blog post not found.', 404);
        }

        return $this->successResponse(new BlogPostResource($post));
    }

    public function categories(): JsonResponse
    {
        $categories = $this->service->getActiveCategories();

        return $this->successResponse(BlogCategoryResource::collection($categories));
    }

    public function tags(): JsonResponse
    {
        $tags = $this->service->getActiveTags();

        return $this->successResponse(BlogTagResource::collection($tags));
    }
}
