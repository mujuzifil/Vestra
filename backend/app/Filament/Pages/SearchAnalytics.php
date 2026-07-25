<?php

namespace App\Filament\Pages;

use App\Models\SearchAnalytic;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SearchAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.search-analytics';

    public function getTitle(): string
    {
        return 'Search Analytics';
    }

    public function getViewData(): array
    {
        $days = 30;

        $summary = [
            'total_searches' => SearchAnalytic::where('searched_at', '>=', now()->subDays($days))->count(),
            'unique_terms' => SearchAnalytic::where('searched_at', '>=', now()->subDays($days))->distinct('term')->count('term'),
            'zero_result_searches' => SearchAnalytic::where('searched_at', '>=', now()->subDays($days))->where('results_count', 0)->count(),
        ];

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

        return [
            'summary' => $summary,
            'topTerms' => $topTerms,
            'trend' => $trend,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
