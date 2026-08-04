<?php

namespace App\Services\Admin;

use App\Enums\FeedbackCategory;
use App\Enums\FeedbackStatus;
use App\Models\CustomerFeedback;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class FeedbackAdminService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateFeedback(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryFeedback($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryFeedback(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = CustomerFeedback::query()
            ->with('user')
            ->when($filters['search'] ?? null, function (Builder $q, string $term): Builder {
                return $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('subject', 'like', "%{$term}%")
                        ->orWhere('message', 'like', "%{$term}%")
                        ->orWhereHas('user', fn (Builder $uq) => $uq->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['category'] ?? null, fn (Builder $q, array $categories) => $q->whereIn('category', $categories))
            ->when($filters['priority'] ?? null, fn (Builder $q, array $priorities) => $q->whereIn('priority', $priorities))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('created_at', '<=', $until));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $monthStart = now()->copy()->startOfMonth();

        $totalCurrent = CustomerFeedback::query()->count();
        $totalPrev = CustomerFeedback::query()->where('created_at', '<', $monthStart)->count();

        $newCurrent = CustomerFeedback::query()->where('status', FeedbackStatus::NEW->value)->count();
        $newPrev = CustomerFeedback::query()->where('status', FeedbackStatus::NEW->value)->where('created_at', '<', $monthStart)->count();

        $inProgressCurrent = CustomerFeedback::query()->where('status', FeedbackStatus::IN_PROGRESS->value)->count();
        $inProgressPrev = CustomerFeedback::query()->where('status', FeedbackStatus::IN_PROGRESS->value)->where('created_at', '<', $monthStart)->count();

        $resolvedCurrent = CustomerFeedback::query()->where('status', FeedbackStatus::RESOLVED->value)->count();
        $resolvedPrev = CustomerFeedback::query()->where('status', FeedbackStatus::RESOLVED->value)->where('created_at', '<', $monthStart)->count();

        $praiseCurrent = CustomerFeedback::query()->where('category', FeedbackCategory::PRAISE->value)->count();
        $praisePrev = CustomerFeedback::query()->where('category', FeedbackCategory::PRAISE->value)->where('created_at', '<', $monthStart)->count();

        $complaintCurrent = CustomerFeedback::query()->where('category', FeedbackCategory::COMPLAINT->value)->count();
        $complaintPrev = CustomerFeedback::query()->where('category', FeedbackCategory::COMPLAINT->value)->where('created_at', '<', $monthStart)->count();

        return [
            $this->buildCard('Total', $totalCurrent, $totalPrev, 'heroicon-o-chat-bubble-left-right', 'primary'),
            $this->buildCard('New', $newCurrent, $newPrev, 'heroicon-o-inbox', 'info'),
            $this->buildCard('In Progress', $inProgressCurrent, $inProgressPrev, 'heroicon-o-arrow-path', 'warning'),
            $this->buildCard('Resolved', $resolvedCurrent, $resolvedPrev, 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Praise', $praiseCurrent, $praisePrev, 'heroicon-o-star', 'success'),
            $this->buildCard('Complaints', $complaintCurrent, $complaintPrev, 'heroicon-o-exclamation-triangle', 'danger'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(CustomerFeedback $feedback): array
    {
        $feedback->load('user');

        return [
            'id' => $feedback->id,
            'subject' => $feedback->subject,
            'message' => $feedback->message,
            'category' => $feedback->category,
            'category_label' => $feedback->categoryLabel(),
            'status' => $feedback->status,
            'status_label' => $feedback->statusLabel(),
            'status_color' => $feedback->statusColor(),
            'priority' => $feedback->priority,
            'priority_label' => $feedback->priorityLabel(),
            'priority_color' => $feedback->priorityColor(),
            'read_at' => $feedback->read_at,
            'created_at' => $feedback->created_at,
            'updated_at' => $feedback->updated_at,
            'user' => $feedback->user ? [
                'id' => $feedback->user->id,
                'name' => $feedback->user->name,
                'email' => $feedback->user->email,
                'initials' => $feedback->user->initials(),
            ] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryFeedback($filters, 'created_at', 'desc')
            ->get()
            ->map(fn (CustomerFeedback $feedback) => [
                'customer' => $feedback->user?->name ?? '—',
                'email' => $feedback->user?->email ?? '—',
                'category' => $feedback->categoryLabel(),
                'subject' => $feedback->subject,
                'status' => $feedback->statusLabel(),
                'priority' => $feedback->priorityLabel(),
                'read' => $feedback->isRead() ? 'Yes' : 'No',
                'submitted_at' => $feedback->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'subject' => $query->orderBy('subject', $direction),
            'status' => $query->orderBy('status', $direction),
            'priority' => $query->orderBy('priority', $direction),
            'category' => $query->orderBy('category', $direction),
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
            return ['value' => '0%', 'label' => 'No change', 'positive' => true];
        }

        if ($previous <= 0) {
            return ['value' => '—', 'label' => 'No comparison available', 'positive' => true];
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
