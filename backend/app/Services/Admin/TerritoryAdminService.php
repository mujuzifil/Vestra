<?php

namespace App\Services\Admin;

use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorServiceArea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TerritoryAdminService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateCoverageAreas(array $filters = [], string $sort = 'region', string $direction = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryCoverageAreas($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryCoverageAreas(array $filters = [], string $sort = 'region', string $direction = 'asc'): Builder
    {
        $query = DistributorServiceArea::query()
            ->with(['distributor', 'branch'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where(function (Builder $sub) use ($term): void {
                    $sub->where('region', 'like', "%{$term}%")
                        ->orWhere('district', 'like', "%{$term}%")
                        ->orWhereHas('distributor', function (Builder $d) use ($term): void {
                            $d->where('company_name', 'like', "%{$term}%");
                        });
                });
            })
            ->when($filters['region'] ?? null, fn (Builder $q, array $regions) => $q->whereIn('region', $regions))
            ->when($filters['district'] ?? null, fn (Builder $q, array $districts) => $q->whereIn('district', $districts))
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['distributor_id'] ?? null, fn (Builder $q, int $id) => $q->where('distributor_id', $id));

        return $this->applyCoverageSorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCoverageKpiCards(): array
    {
        $totalAreas = DistributorServiceArea::query()->count();
        $coveredAreas = DistributorServiceArea::query()->where('status', 'covered')->count();
        $comingSoon = DistributorServiceArea::query()->where('status', 'coming_soon')->count();
        $distinctRegions = DistributorServiceArea::query()->distinct('region')->count('region');
        $activePartners = DistributorServiceArea::query()->distinct('distributor_id')->count('distributor_id');

        return [
            $this->buildCard('Coverage Areas', $totalAreas, 0, 'total mapped', 'heroicon-o-map', 'primary', false),
            $this->buildCard('Covered', $coveredAreas, 0, 'districts served', 'heroicon-o-check-circle', 'success', false),
            $this->buildCard('Coming Soon', $comingSoon, 0, 'planned expansion', 'heroicon-o-clock', 'warning', false),
            $this->buildCard('Regions', $distinctRegions, 0, 'distinct regions', 'heroicon-o-globe-alt', 'info', false),
            $this->buildCard('Active Partners', $activePartners, 0, 'with coverage', 'heroicon-o-building-storefront', 'primary', false),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getCoverageByRegion(array $filters = []): Collection
    {
        return $this->queryCoverageAreas($filters)
            ->selectRaw('region, district, status, COUNT(*) as partner_count')
            ->groupBy('region', 'district', 'status')
            ->orderBy('region')
            ->orderBy('district')
            ->get()
            ->map(fn ($row) => [
                'region' => $row->region,
                'district' => $row->district,
                'status' => $row->status,
                'partner_count' => (int) $row->partner_count,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCoverageFilterOptions(): array
    {
        $areas = DistributorServiceArea::query();

        return [
            'regions' => (clone $areas)->whereNotNull('region')->distinct()->orderBy('region')->pluck('region')->toArray(),
            'districts' => (clone $areas)->whereNotNull('district')->distinct()->orderBy('district')->pluck('district')->toArray(),
            'statuses' => [
                'covered' => 'Covered',
                'coming_soon' => 'Coming Soon',
                'planned' => 'Planned',
            ],
            'distributors' => Distributor::query()
                ->whereHas('serviceAreas')
                ->orderBy('company_name')
                ->get(['id', 'company_name'])
                ->map(fn (Distributor $distributor) => ['id' => $distributor->id, 'name' => $distributor->company_name])
                ->toArray(),
        ];
    }

    private function applyCoverageSorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'region' => $query->orderBy('region', $direction)->orderBy('district', $direction),
            'district' => $query->orderBy('district', $direction),
            'status' => $query->orderBy('status', $direction),
            'distributor' => $query->orderBy(
                Distributor::select('company_name')
                    ->whereColumn('distributors.id', 'distributor_service_areas.distributor_id')
                    ->limit(1),
                $direction
            ),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('region', 'asc')->orderBy('district', 'asc'),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateBranches(array $filters = [], string $sort = 'name', string $direction = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryBranches($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryBranches(array $filters = [], string $sort = 'name', string $direction = 'asc'): Builder
    {
        $query = DistributorBranch::query()
            ->with(['distributor', 'serviceAreas'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where(function (Builder $sub) use ($term): void {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('manager_name', 'like', "%{$term}%")
                        ->orWhere('city', 'like', "%{$term}%")
                        ->orWhere('district', 'like', "%{$term}%")
                        ->orWhere('country', 'like', "%{$term}%")
                        ->orWhereHas('distributor', function (Builder $d) use ($term): void {
                            $d->where('company_name', 'like', "%{$term}%");
                        });
                });
            })
            ->when($filters['country'] ?? null, fn (Builder $q, array $countries) => $q->whereIn('country', $countries))
            ->when($filters['district'] ?? null, fn (Builder $q, array $districts) => $q->whereIn('district', $districts))
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['distributor_id'] ?? null, fn (Builder $q, int $id) => $q->where('distributor_id', $id));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();

        $totalCurrent = DistributorBranch::query()->count();
        $totalPrevious = DistributorBranch::query()
            ->whereDate('created_at', '<', $currentMonthStart)
            ->count();

        $activeCurrent = DistributorBranch::query()->where('status', 'active')->count();
        $activePrevious = DistributorBranch::query()
            ->where('status', 'active')
            ->whereDate('created_at', '<', $currentMonthStart)
            ->count();

        $inactiveCurrent = DistributorBranch::query()->where('status', 'inactive')->count();

        $distinctDistributors = DistributorBranch::query()->distinct('distributor_id')->count('distributor_id');

        $distinctCountries = DistributorBranch::query()
            ->whereNotNull('country')
            ->distinct('country')
            ->count('country');

        return [
            $this->buildCard('Total Branches', $totalCurrent, $totalPrevious, 'vs last month', 'heroicon-o-building-storefront', 'primary'),
            $this->buildCard('Active', $activeCurrent, $activePrevious, 'vs last month', 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Inactive', $inactiveCurrent, 0, 'vs last month', 'heroicon-o-pause-circle', 'danger', false),
            $this->buildCard('Distinct Distributors', $distinctDistributors, 0, 'vs last month', 'heroicon-o-building-office-2', 'info', false),
            $this->buildCard('Distinct Countries', $distinctCountries, 0, 'vs last month', 'heroicon-o-globe-alt', 'warning', false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(DistributorBranch $branch): array
    {
        $branch->loadMissing(['distributor', 'serviceAreas']);

        $hasCoordinates = $branch->latitude !== null && $branch->longitude !== null;

        return [
            'id' => $branch->id,
            'name' => $branch->name,
            'manager_name' => $branch->manager_name,
            'phone' => $branch->phone,
            'email' => $branch->email,
            'country' => $branch->country,
            'district' => $branch->district,
            'city' => $branch->city,
            'address' => $branch->address,
            'formatted_address' => $branch->formattedAddress(),
            'latitude' => $branch->latitude,
            'longitude' => $branch->longitude,
            'has_coordinates' => $hasCoordinates,
            'delivery_notes' => $branch->delivery_notes,
            'status' => $branch->status,
            'is_default' => (bool) $branch->is_default,
            'created_at' => $branch->created_at,
            'updated_at' => $branch->updated_at,
            'distributor' => $branch->distributor ? [
                'id' => $branch->distributor->id,
                'company_name' => $branch->distributor->company_name,
                'trading_name' => $branch->distributor->trading_name,
                'status' => $branch->distributor->status?->value ?? $branch->distributor->status,
                'email' => $branch->distributor->email,
                'phone' => $branch->distributor->phone,
            ] : null,
            'service_areas' => $branch->serviceAreas->map(fn ($area) => [
                'id' => $area->id,
                'region' => $area->region,
                'district' => $area->district,
                'status' => $area->status,
            ])->toArray(),
        ];
    }

    /**
     * Branches that can be safely plotted on the map — real coordinates only.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function getMappableBranches(array $filters = []): Collection
    {
        return $this->queryBranches($filters, 'name', 'asc')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(fn (DistributorBranch $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'status' => $branch->status,
                'latitude' => (float) $branch->latitude,
                'longitude' => (float) $branch->longitude,
                'city' => $branch->city,
                'country' => $branch->country,
                'distributor_name' => $branch->distributor?->company_name,
            ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function countUnmappedBranches(array $filters = []): int
    {
        return $this->queryBranches($filters)
            ->where(fn (Builder $q) => $q->whereNull('latitude')->orWhereNull('longitude'))
            ->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportBranches(array $filters = []): array
    {
        return $this->queryBranches($filters, 'name', 'asc')
            ->get()
            ->map(fn (DistributorBranch $branch) => [
                'name' => $branch->name,
                'distributor' => $branch->distributor?->company_name,
                'manager_name' => $branch->manager_name,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'country' => $branch->country,
                'district' => $branch->district,
                'city' => $branch->city,
                'address' => $branch->address,
                'latitude' => $branch->latitude,
                'longitude' => $branch->longitude,
                'status' => ucfirst((string) $branch->status),
                'is_default' => $branch->is_default ? 'Yes' : 'No',
                'created_at' => $branch->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        $branches = DistributorBranch::query();

        return [
            'countries' => (clone $branches)->whereNotNull('country')->distinct()->orderBy('country')->pluck('country')->toArray(),
            'districts' => (clone $branches)->whereNotNull('district')->distinct()->orderBy('district')->pluck('district')->toArray(),
            'statuses' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            'distributors' => Distributor::query()
                ->orderBy('company_name')
                ->get(['id', 'company_name'])
                ->map(fn (Distributor $distributor) => ['id' => $distributor->id, 'name' => $distributor->company_name])
                ->toArray(),
        ];
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'distributor' => $query->orderBy(
                Distributor::select('company_name')
                    ->whereColumn('distributors.id', 'distributor_branches.distributor_id')
                    ->limit(1),
                $direction
            ),
            'country' => $query->orderBy('country', $direction),
            'district' => $query->orderBy('district', $direction),
            'city' => $query->orderBy('city', $direction),
            'status' => $query->orderBy('status', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('name', 'asc'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, float $previous, string $comparisonLabel, string $icon, string $color, bool $trendAvailable = true): array
    {
        $trend = $trendAvailable ? $this->calculateTrend($current, $previous) : [
            'value' => '—',
            'label' => 'No comparison',
            'positive' => true,
        ];

        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => $trend['value'],
            'trend_label' => $trend['label'].' '.$comparisonLabel,
            'trend_positive' => $trend['positive'],
            'trend_available' => $trendAvailable && $previous > 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateTrend(float $current, float $previous): array
    {
        if ($previous <= 0 && $current <= 0) {
            return [
                'value' => '0%',
                'label' => 'No change',
                'positive' => true,
            ];
        }

        if ($previous <= 0) {
            return [
                'value' => '+100%',
                'label' => 'Up',
                'positive' => true,
            ];
        }

        $change = (($current - $previous) / $previous) * 100;
        $positive = $change >= 0;

        return [
            'value' => sprintf('%s%.1f%%', $positive ? '+' : '', $change),
            'label' => $positive ? 'Up' : 'Down',
            'positive' => $positive,
        ];
    }
}
