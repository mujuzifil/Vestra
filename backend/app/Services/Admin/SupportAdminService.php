<?php

namespace App\Services\Admin;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SupportAdminService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateTickets(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryTickets($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryTickets(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = SupportTicket::query()
            ->with(['user', 'assignedStaff'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('reference_number', 'like', "%{$term}%")
                        ->orWhere('subject', 'like', "%{$term}%")
                        ->orWhere('message', 'like', "%{$term}%")
                        ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['priority'] ?? null, fn (Builder $q, array $priorities) => $q->whereIn('priority', $priorities))
            ->when($filters['enquiry_type'] ?? null, fn (Builder $q, array $types) => $q->whereIn('enquiry_type', $types))
            ->when($filters['assigned_to'] ?? null, fn (Builder $q, int $id) => $q->where('assigned_to', $id))
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

        $total = SupportTicket::query()->count();
        $totalPrev = SupportTicket::query()->where('created_at', '<', $currentMonthStart)->count();

        $open = SupportTicket::query()->where('status', 'open')->count();
        $openPrev = SupportTicket::query()->where('status', 'open')->where('created_at', '<', $currentMonthStart)->count();

        $inProgress = SupportTicket::query()->where('status', 'in_progress')->count();
        $inProgressPrev = SupportTicket::query()->where('status', 'in_progress')->where('created_at', '<', $currentMonthStart)->count();

        $resolved = SupportTicket::query()->where('status', 'resolved')->count();
        $resolvedPrev = SupportTicket::query()->where('status', 'resolved')->where('created_at', '<', $currentMonthStart)->count();

        $closed = SupportTicket::query()->where('status', 'closed')->count();
        $closedPrev = SupportTicket::query()->where('status', 'closed')->where('created_at', '<', $currentMonthStart)->count();

        $cards = [
            $this->buildCard('Total', $total, $totalPrev, 'heroicon-o-inbox-stack', 'primary'),
            $this->buildCard('Open', $open, $openPrev, 'heroicon-o-envelope-open', 'warning'),
            $this->buildCard('In Progress', $inProgress, $inProgressPrev, 'heroicon-o-arrow-path', 'info'),
            $this->buildCard('Resolved', $resolved, $resolvedPrev, 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Closed', $closed, $closedPrev, 'heroicon-o-lock-closed', 'primary'),
        ];

        $avgResolution = $this->getAvgResolutionHours();
        if ($avgResolution !== null) {
            $cards[] = [
                'label' => 'Avg Resolution',
                'value' => $avgResolution < 48
                    ? round($avgResolution, 1).'h'
                    : round($avgResolution / 24, 1).'d',
                'icon' => 'heroicon-o-clock',
                'color' => 'info',
                'trend' => '—',
                'trend_label' => 'From resolved_at',
                'trend_positive' => true,
                'trend_available' => false,
            ];
        }

        return $cards;
    }

    public function getDetail(SupportTicket $ticket): array
    {
        $ticket->load(['user', 'assignedStaff', 'replies.user', 'replies.staff']);

        return [
            'id' => $ticket->id,
            'reference_number' => $ticket->reference_number,
            'subject' => $ticket->subject,
            'enquiry_type' => $ticket->enquiry_type,
            'message' => $ticket->message,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'assigned_to' => $ticket->assigned_to,
            'attachments' => $ticket->attachments ?? [],
            'resolved_at' => $ticket->resolved_at,
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at,
            'user' => $ticket->user ? [
                'id' => $ticket->user->id,
                'name' => $ticket->user->name,
                'email' => $ticket->user->email,
                'initials' => $ticket->user->initials(),
            ] : null,
            'assignee' => $ticket->assignedStaff ? [
                'id' => $ticket->assignedStaff->id,
                'name' => $ticket->assignedStaff->name,
                'email' => $ticket->assignedStaff->email,
                'initials' => $ticket->assignedStaff->initials(),
            ] : null,
            'replies' => $ticket->replies->map(fn (SupportTicketReply $reply) => [
                'id' => $reply->id,
                'message' => $reply->message,
                'is_internal' => $reply->is_internal,
                'attachments' => $reply->attachments ?? [],
                'created_at' => $reply->created_at,
                'author_name' => $reply->staff?->name ?? $reply->user?->name ?? 'Unknown',
                'author_initials' => $reply->staff ? $reply->staff->initials() : ($reply->user ? $reply->user->initials() : '?'),
                'is_staff' => $reply->staff_id !== null,
            ])->toArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'statuses' => ['open', 'in_progress', 'resolved', 'closed'],
            'priorities' => ['low', 'medium', 'high', 'urgent'],
            'enquiry_types' => SupportTicket::query()
                ->whereNotNull('enquiry_type')
                ->where('enquiry_type', '!=', '')
                ->distinct()
                ->orderBy('enquiry_type')
                ->pluck('enquiry_type')
                ->toArray(),
            'assignees' => User::query()
                ->where(function (Builder $q): void {
                    $q->where('is_admin', true)
                        ->orWhereIn('id', SupportTicket::query()->whereNotNull('assigned_to')->distinct()->pluck('assigned_to'));
                })
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])
                ->unique('id')
                ->values()
                ->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryTickets($filters, 'created_at', 'desc')
            ->get()
            ->map(fn (SupportTicket $ticket) => [
                'reference_number' => $ticket->reference_number,
                'subject' => $ticket->subject,
                'enquiry_type' => $ticket->enquiry_type,
                'status' => ucfirst(str_replace('_', ' ', $ticket->status)),
                'priority' => ucfirst($ticket->priority),
                'customer_name' => $ticket->user?->name,
                'customer_email' => $ticket->user?->email,
                'assigned_to' => $ticket->assignedStaff?->name,
                'resolved_at' => $ticket->resolved_at?->format('Y-m-d H:i:s'),
                'created_at' => $ticket->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    private function getAvgResolutionHours(): ?float
    {
        $avg = SupportTicket::query()
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, resolved_at)) as avg_seconds')
            ->value('avg_seconds');

        if ($avg === null) {
            return null;
        }

        return (float) $avg / 3600;
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'subject' => $query->orderBy('subject', $direction),
            'status' => $query->orderBy('status', $direction),
            'priority' => $query->orderBy('priority', $direction),
            'enquiry_type' => $query->orderBy('enquiry_type', $direction),
            'reference_number' => $query->orderBy('reference_number', $direction),
            'resolved_at' => $query->orderBy('resolved_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            'assigned_to' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'support_tickets.assigned_to')
                    ->limit(1),
                $direction
            ),
            default => $query->orderBy('created_at', $direction),
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
