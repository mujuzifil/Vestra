<?php

namespace App\Services\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchService
{
    public function __construct(private readonly SearchProviderInterface $provider) {}

    public function searchProducts(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->provider->searchProducts($filters, $perPage);
    }

    public function suggestions(string $term, int $limit = 8): array
    {
        return $this->provider->suggestions($term, $limit);
    }

    public function recordSearch(string $term, int $resultsCount, ?int $userId = null, ?string $sessionId = null): void
    {
        $this->provider->recordSearch($term, $resultsCount, $userId, $sessionId);
    }
}
