<?php

namespace App\Services\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SearchProviderInterface
{
    /**
     * Search active products using the provided filters.
     *
     * @param array<string, mixed> $filters
     */
    public function searchProducts(array $filters, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get autocomplete suggestions for a partial search term.
     *
     * @return array<int, array{name: string, slug: string, type: string, id?: int}>
     */
    public function suggestions(string $term, int $limit = 8): array;

    /**
     * Record a search event for analytics.
     */
    public function recordSearch(string $term, int $resultsCount, ?int $userId = null, ?string $sessionId = null): void;
}
