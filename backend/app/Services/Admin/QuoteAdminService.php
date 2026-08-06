<?php

namespace App\Services\Admin;

use App\Enums\QuoteRequestPriority;
use App\Enums\QuoteRequestStatus;
use App\Events\Notification\QuoteRequestStatusChanged;
use App\Models\AuditLog;
use App\Models\QuoteRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class QuoteAdminService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateQuotes(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryQuotes($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryQuotes(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = QuoteRequest::query()
            ->with(['items.product', 'assignedUser', 'user.companyProfile', 'companyProfile'])
            ->withCount('items')
            ->when($filters['search'] ?? null, fn (Builder $q, string $term) => $q->search($term))
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->statusIn($statuses))
            ->when($filters['priority'] ?? null, fn (Builder $q, array $priorities) => $q->priorityIn($priorities))
            ->when($filters['assigned_to'] ?? null, fn (Builder $q, int $id) => $q->where('assigned_to', $id))
            ->when($filters['company_profile_id'] ?? null, fn (Builder $q, int $id) => $q->where('company_profile_id', $id))
            ->when($filters['district'] ?? null, fn (Builder $q, array $districts) => $q->whereIn('district', $districts))
            ->when($filters['city'] ?? null, fn (Builder $q, array $cities) => $q->whereIn('city', $cities))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('created_at', '<=', $until))
            ->when($filters['close_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('expected_close_date', '>=', $from))
            ->when($filters['close_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('expected_close_date', '<=', $until))
            ->when(isset($filters['min_value']) && $filters['min_value'] !== null && $filters['min_value'] !== '', fn (Builder $q) => $q->where('estimated_value', '>=', (float) $filters['min_value']))
            ->when(isset($filters['max_value']) && $filters['max_value'] !== null && $filters['max_value'] !== '', fn (Builder $q) => $q->where('estimated_value', '<=', (float) $filters['max_value']));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $currentMonthStart = now()->copy()->startOfMonth();
        $previousMonthStart = now()->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        $totalCurrent = QuoteRequest::query()->count();
        $totalPrevious = QuoteRequest::query()
            ->where('created_at', '<', $currentMonthStart)
            ->count();

        $pendingCurrent = QuoteRequest::query()->where('status', QuoteRequestStatus::PENDING)->count();
        $pendingPrevious = QuoteRequest::query()
            ->where('status', QuoteRequestStatus::PENDING)
            ->where('created_at', '<', $currentMonthStart)
            ->count();

        $approvedCurrent = QuoteRequest::query()->where('status', QuoteRequestStatus::APPROVED)->count();
        $approvedPrevious = QuoteRequest::query()
            ->where('status', QuoteRequestStatus::APPROVED)
            ->where('created_at', '<', $currentMonthStart)
            ->count();

        $declinedCurrent = QuoteRequest::query()->where('status', QuoteRequestStatus::DECLINED)->count();
        $declinedPrevious = QuoteRequest::query()
            ->where('status', QuoteRequestStatus::DECLINED)
            ->where('created_at', '<', $currentMonthStart)
            ->count();

        $valueCurrent = (float) QuoteRequest::query()
            ->whereNotNull('estimated_value')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('estimated_value');

        $valuePrevious = (float) QuoteRequest::query()
            ->whereNotNull('estimated_value')
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('estimated_value');

        $hasValueHistory = QuoteRequest::query()
            ->whereNotNull('estimated_value')
            ->where('created_at', '<', $currentMonthStart)
            ->exists();

        return [
            $this->buildCard('Total Quotes', $totalCurrent, $totalPrevious, 'vs last month', 'heroicon-o-document-text', 'primary'),
            $this->buildCard('Pending', $pendingCurrent, $pendingPrevious, 'vs last month', 'heroicon-o-clock', 'warning'),
            $this->buildCard('Approved', $approvedCurrent, $approvedPrevious, 'vs last month', 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Declined', $declinedCurrent, $declinedPrevious, 'vs last month', 'heroicon-o-x-circle', 'danger'),
            $this->buildValueCard('Total Value (MTD)', $valueCurrent, $valuePrevious, $hasValueHistory, 'heroicon-o-banknotes', 'info'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getQuoteDetail(QuoteRequest $quote): array
    {
        $quote->load(['items.product', 'assignedUser', 'user.companyProfile', 'companyProfile']);

        $companyProfile = $quote->companyProfile ?? $quote->user?->companyProfile;

        return [
            'id' => $quote->id,
            'reference_number' => $quote->reference_number,
            'status' => $quote->status,
            'priority' => $quote->priority,
            'priority_label' => $quote->priorityLabel(),
            'priority_color' => $quote->priorityColor(),
            'estimated_value' => $quote->estimated_value,
            'expected_close_date' => $quote->expected_close_date,
            'preferred_delivery_date' => $quote->preferred_delivery_date,
            'source' => $quote->source,
            'requirements' => $quote->requirements,
            'admin_notes' => $quote->admin_notes,
            'district' => $quote->district,
            'city' => $quote->city,
            'address' => $quote->address,
            'delivery_location' => $quote->delivery_location,
            'created_at' => $quote->created_at,
            'updated_at' => $quote->updated_at,
            'company' => [
                'name' => $quote->company_name,
                'profile_id' => $companyProfile?->id,
                'industry' => $companyProfile?->industry,
            ],
            'contact' => [
                'name' => $quote->full_name,
                'email' => $quote->email,
                'phone' => $quote->phone,
                'user_id' => $quote->user_id,
            ],
            'sales_rep' => $quote->assignedUser ? [
                'id' => $quote->assignedUser->id,
                'name' => $quote->assignedUser->name,
                'email' => $quote->assignedUser->email,
                'initials' => $quote->assignedUser->initials(),
            ] : null,
            'products' => $quote->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name ?? $item->product?->name,
                'package_size' => $item->package_size,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
            ])->toArray(),
            'attachments' => $quote->attachmentList(),
            'support_tickets' => $quote->user_id
                ? SupportTicket::query()
                    ->where('user_id', $quote->user_id)
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (SupportTicket $ticket) => [
                        'id' => $ticket->id,
                        'reference_number' => $ticket->reference_number,
                        'subject' => $ticket->subject,
                        'status' => $ticket->status,
                        'priority' => $ticket->priority,
                        'created_at' => $ticket->created_at,
                    ])->toArray()
                : [],
            'recent_activity' => AuditLog::query()
                ->where(function (Builder $q) use ($quote): void {
                    $q->where(function (Builder $sub) use ($quote): void {
                        $sub->where('subject_type', QuoteRequest::class)
                            ->where('subject_id', $quote->id);
                    });

                    if ($quote->user_id) {
                        $q->orWhere(function (Builder $sub) use ($quote): void {
                            $sub->where('user_id', $quote->user_id)
                                ->whereIn('action', ['quote_submitted', 'quote_viewed', 'quote.updated', 'quote.status_changed']);
                        });
                    }
                })
                ->with('user')
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (AuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'user' => $log->user?->name ?? 'System',
                    'created_at' => $log->created_at,
                ])->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateQuote(QuoteRequest $quote, array $data): QuoteRequest
    {
        return DB::transaction(function () use ($quote, $data) {
            $previousStatus = $quote->status?->value;

            $quote->fill([
                'status' => $data['status'] ?? $quote->status?->value,
                'priority' => array_key_exists('priority', $data) ? $data['priority'] : $quote->priority,
                'estimated_value' => array_key_exists('estimated_value', $data) ? $data['estimated_value'] : $quote->estimated_value,
                'expected_close_date' => array_key_exists('expected_close_date', $data) ? $data['expected_close_date'] : $quote->expected_close_date,
                'assigned_to' => array_key_exists('assigned_to', $data) ? $data['assigned_to'] : $quote->assigned_to,
                'admin_notes' => array_key_exists('admin_notes', $data) ? $data['admin_notes'] : $quote->admin_notes,
                'requirements' => array_key_exists('requirements', $data) ? $data['requirements'] : $quote->requirements,
            ]);

            $quote->save();

            $statusChanged = $previousStatus !== $quote->status?->value;
            $action = $statusChanged ? 'quote.status_changed' : 'quote.updated';

            AuditService::log(
                auth()->user(),
                $action,
                $quote,
                [
                    'reference_number' => $quote->reference_number,
                    'status' => $quote->status?->value,
                    'previous_status' => $previousStatus,
                    'changes' => $quote->getChanges(),
                ]
            );

            $fresh = $quote->fresh(['items.product', 'assignedUser', 'user.companyProfile', 'companyProfile']);

            if ($statusChanged && $previousStatus !== null && $fresh->status?->value !== null) {
                event(new QuoteRequestStatusChanged(
                    $fresh,
                    $previousStatus,
                    $fresh->status->value,
                ));
            }

            return $fresh;
        });
    }

    public function updateStatus(QuoteRequest $quote, QuoteRequestStatus $status): QuoteRequest
    {
        return $this->updateQuote($quote, ['status' => $status->value]);
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportQuotes(array $filters = []): array
    {
        return $this->queryQuotes($filters, 'created_at', 'desc')
            ->get()
            ->map(fn (QuoteRequest $quote) => [
                'reference_number' => $quote->reference_number,
                'company_name' => $quote->company_name,
                'contact_name' => $quote->full_name,
                'email' => $quote->email,
                'phone' => $quote->phone,
                'status' => $quote->statusLabel(),
                'priority' => $quote->priorityLabel(),
                'estimated_value' => $quote->estimated_value,
                'expected_close_date' => $quote->expected_close_date?->format('Y-m-d'),
                'sales_rep' => $quote->assignedUser?->name,
                'district' => $quote->district,
                'city' => $quote->city,
                'products' => $quote->items->pluck('product_name')->filter()->implode(', '),
                'created_at' => $quote->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        $quotes = QuoteRequest::query();

        return [
            'districts' => (clone $quotes)->whereNotNull('district')->where('district', '!=', '')->distinct()->orderBy('district')->pluck('district')->toArray(),
            'cities' => (clone $quotes)->whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city')->toArray(),
            'priorities' => QuoteRequestPriority::cases(),
            'sales_reps' => User::query()
                ->where(function (Builder $q): void {
                    $q->where('is_admin', true)
                        ->orWhereHas('roles')
                        ->orWhereIn('id', QuoteRequest::query()->whereNotNull('assigned_to')->distinct()->pluck('assigned_to'));
                })
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])
                ->unique('id')
                ->values()
                ->toArray(),
        ];
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'reference_number' => $query->orderBy('reference_number', $direction),
            'company_name' => $query->orderBy('company_name', $direction),
            'status' => $query->orderBy('status', $direction),
            'priority' => $query->orderBy('priority', $direction),
            'estimated_value' => $query->orderBy('estimated_value', $direction),
            'expected_close_date' => $query->orderBy('expected_close_date', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            'sales_rep' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'quote_requests.assigned_to')
                    ->limit(1),
                $direction
            ),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, float $previous, string $comparisonLabel, string $icon, string $color): array
    {
        $trendAvailable = $previous > 0 || $current > 0;
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
            'trend_label' => $hasComparison ? ($trend['label'].' '.$comparisonLabel) : 'No comparison available',
            'trend_positive' => $trend['positive'],
            'trend_available' => $hasComparison && $trendAvailable,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildValueCard(string $label, float $current, float $previous, bool $hasHistory, string $icon, string $color): array
    {
        $hasComparison = $hasHistory && $previous > 0;

        $trend = $hasComparison ? $this->calculateTrend($current, $previous) : [
            'value' => '—',
            'label' => 'No comparison available',
            'positive' => true,
        ];

        return [
            'label' => $label,
            'value' => $this->formatCurrency($current),
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

    private function formatCurrency(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'UGX '.number_format($amount / 1_000_000_000, 2).'B';
        }

        if ($amount >= 1_000_000) {
            return 'UGX '.number_format($amount / 1_000_000, 1).'M';
        }

        if ($amount >= 1_000) {
            return 'UGX '.number_format($amount / 1_000, 1).'K';
        }

        return 'UGX '.number_format($amount, 0);
    }
}
