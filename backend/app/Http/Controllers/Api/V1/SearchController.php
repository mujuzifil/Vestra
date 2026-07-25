<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SearchAnalytic;
use App\Services\Search\SearchService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SearchService $searchService) {}

    public function autocomplete(Request $request): JsonResponse
    {
        $term = $request->input('q', '');
        if (strlen($term) < 2) {
            return $this->successResponse([]);
        }

        $suggestions = $this->searchService->suggestions($term, 8);

        return $this->successResponse($suggestions);
    }

    public function popular(Request $request): JsonResponse
    {
        $limit = max(1, min(10, $request->integer('limit', 6)));

        $terms = SearchAnalytic::query()
            ->select('term', DB::raw('COUNT(*) as count'))
            ->where('searched_at', '>=', now()->subDays(30))
            ->groupBy('term')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return $this->successResponse($terms);
    }

    public function analytics(Request $request): JsonResponse
    {
        $this->authorize('view reports', 'App\Models\User');

        $days = max(1, min(90, $request->integer('days', 30)));

        $totalSearches = SearchAnalytic::where('searched_at', '>=', now()->subDays($days))->count();
        $uniqueTerms = SearchAnalytic::where('searched_at', '>=', now()->subDays($days))->distinct('term')->count('term');
        $zeroResultSearches = SearchAnalytic::where('searched_at', '>=', now()->subDays($days))->where('results_count', 0)->count();

        $topTerms = SearchAnalytic::query()
            ->select('term', DB::raw('COUNT(*) as count'), DB::raw('AVG(results_count) as avg_results'))
            ->where('searched_at', '>=', now()->subDays($days))
            ->groupBy('term')
            ->orderByDesc('count')
            ->limit(20)
            ->get();

        $trend = SearchAnalytic::query()
            ->select(DB::raw('DATE(searched_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('searched_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->successResponse([
            'summary' => [
                'total_searches' => $totalSearches,
                'unique_terms' => $uniqueTerms,
                'zero_result_searches' => $zeroResultSearches,
            ],
            'top_terms' => $topTerms,
            'trend' => $trend,
        ]);
    }
}
