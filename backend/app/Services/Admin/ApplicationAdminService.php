<?php

namespace App\Services\Admin;

use App\Enums\Priority;
use App\Models\Distributor;
use App\Models\DistributorRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ApplicationAdminService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateApplications(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryApplications($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryApplications(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = DistributorRequest::query()
            ->with('distributor')
            ->when($filters['search'] ?? null, fn (Builder $q, string $term) => $q->search($term))
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->statusIn($statuses))
            ->when($filters['priority'] ?? null, fn (Builder $q, array $priorities) => $q->priorityIn($priorities))
            ->when($filters['country'] ?? null, fn (Builder $q, array $countries) => $q->whereIn('country', $countries))
            ->when($filters['region'] ?? null, fn (Builder $q, array $regions) => $q->whereIn('region', $regions))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('created_at', '<=', $until));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $currentMonthStart = now()->copy()->startOfMonth();

        $totalCurrent = DistributorRequest::query()->count();
        $totalPrevious = DistributorRequest::query()->where('created_at', '<', $currentMonthStart)->count();

        $pendingCurrent = DistributorRequest::query()->pending()->count();
        $pendingPrevious = DistributorRequest::query()->pending()->where('created_at', '<', $currentMonthStart)->count();

        $underReviewCurrent = DistributorRequest::query()->underReview()->count();
        $underReviewPrevious = DistributorRequest::query()->underReview()->where('created_at', '<', $currentMonthStart)->count();

        $infoRequestedCurrent = DistributorRequest::query()->informationRequested()->count();
        $infoRequestedPrevious = DistributorRequest::query()->informationRequested()->where('created_at', '<', $currentMonthStart)->count();

        $approvedCurrent = DistributorRequest::query()->approved()->count();
        $approvedPrevious = DistributorRequest::query()->approved()->where('created_at', '<', $currentMonthStart)->count();

        $rejectedCurrent = DistributorRequest::query()->rejected()->count();
        $rejectedPrevious = DistributorRequest::query()->rejected()->where('created_at', '<', $currentMonthStart)->count();

        return [
            $this->buildCard('Total', $totalCurrent, $totalPrevious, 'heroicon-o-inbox-stack', 'primary'),
            $this->buildCard('Pending', $pendingCurrent, $pendingPrevious, 'heroicon-o-clock', 'warning'),
            $this->buildCard('Under Review', $underReviewCurrent, $underReviewPrevious, 'heroicon-o-magnifying-glass', 'info'),
            $this->buildCard('Information Requested', $infoRequestedCurrent, $infoRequestedPrevious, 'heroicon-o-question-mark-circle', 'info'),
            $this->buildCard('Approved', $approvedCurrent, $approvedPrevious, 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Rejected', $rejectedCurrent, $rejectedPrevious, 'heroicon-o-x-circle', 'danger'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(DistributorRequest $application): array
    {
        $distributor = Distributor::query()
            ->where('distributor_request_id', $application->id)
            ->first();

        $documents = collect($application->documents ?? [])
            ->map(function ($document) {
                if (is_string($document)) {
                    return [
                        'name' => basename($document) ?: $document,
                        'url' => null,
                        'path' => $document,
                    ];
                }

                if (! is_array($document)) {
                    return null;
                }

                return [
                    'name' => $document['name'] ?? $document['filename'] ?? 'Document',
                    'url' => $document['url'] ?? null,
                    'path' => $document['path'] ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $application->id,
            'company_name' => $application->company_name,
            'business_type' => $application->business_type,
            'years_in_operation' => $application->years_in_operation,
            'contact_person' => $application->contact_person,
            'email' => $application->email,
            'phone' => $application->phone,
            'address' => $application->address,
            'country' => $application->country,
            'region' => $application->region,
            'territory' => $application->target_region ?: $application->region,
            'formatted_address' => $application->formattedAddress(),
            'business_description' => $application->business_description,
            'products_interested_in' => $application->products_interested_in,
            'target_region' => $application->target_region,
            'estimated_volume' => $application->estimated_volume,
            'existing_customer' => $application->isExistingCustomer(),
            'previous_applications' => $application->previous_applications,
            'status' => $application->status,
            'status_label' => $application->statusLabel(),
            'status_color' => $application->statusColor(),
            'priority' => $application->priority,
            'priority_label' => $application->priorityLabel(),
            'priority_color' => $application->priorityColor(),
            'internal_notes' => $application->internal_notes,
            'documents' => $documents,
            'created_at' => $application->created_at,
            'updated_at' => $application->updated_at,
            'distributor' => $distributor ? [
                'id' => $distributor->id,
                'status' => $distributor->status,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        $base = DistributorRequest::query();

        return [
            'countries' => (clone $base)->whereNotNull('country')->where('country', '!=', '')->distinct()->orderBy('country')->pluck('country')->toArray(),
            'regions' => (clone $base)->whereNotNull('region')->where('region', '!=', '')->distinct()->orderBy('region')->pluck('region')->toArray(),
            'priorities' => Priority::cases(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryApplications($filters, 'created_at', 'desc')
            ->get()
            ->map(fn (DistributorRequest $application) => [
                'company_name' => $application->company_name,
                'business_type' => $application->business_type,
                'contact_person' => $application->contact_person,
                'email' => $application->email,
                'phone' => $application->phone,
                'country' => $application->country,
                'region' => $application->region,
                'status' => $application->statusLabel(),
                'priority' => $application->priorityLabel(),
                'estimated_volume' => $application->estimated_volume,
                'existing_customer' => $application->isExistingCustomer() ? 'Yes' : 'No',
                'created_at' => $application->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'company_name' => $query->orderBy('company_name', $direction),
            'status' => $query->orderBy('status', $direction),
            'priority' => $query->orderBy('priority', $direction),
            'country' => $query->orderBy('country', $direction),
            'region' => $query->orderBy('region', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, float $previous, string $icon, string $color): array
    {
        $hasComparison = $previous > 0;

        $trend = $hasComparison ? $this->calculateTrend($current, $previous) : [
            'value' => '—',
            'label' => 'No comparison available',
            'positive' => true,
        ];

        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => $trend['value'],
            'trend_label' => $hasComparison ? ($trend['label'].' vs last month') : 'No comparison available',
            'trend_positive' => $trend['positive'],
            'trend_available' => $hasComparison,
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
                'value' => '—',
                'label' => 'No comparison available',
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
