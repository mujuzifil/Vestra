<?php

namespace App\Services\Admin;

use App\Enums\ContactEnquiryType;
use App\Enums\ContactStatus;
use App\Enums\Priority;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EnquiryAdminService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateEnquiries(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryEnquiries($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryEnquiries(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = ContactMessage::query()
            ->with(['assignedTo'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): Builder {
                return $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('company', 'like', "%{$term}%")
                        ->orWhere('subject', 'like', "%{$term}%")
                        ->orWhere('message', 'like', "%{$term}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['source'] ?? null, fn (Builder $q, array $sources) => $q->whereIn('source', $sources))
            ->when($filters['enquiry_type'] ?? null, fn (Builder $q, array $types) => $q->whereIn('enquiry_type', $types))
            ->when($filters['priority'] ?? null, fn (Builder $q, array $priorities) => $q->whereIn('priority', $priorities))
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

        $totalCurrent  = ContactMessage::query()->count();
        $totalPrevious = ContactMessage::query()->where('created_at', '<', $currentMonthStart)->count();

        $newCurrent  = ContactMessage::query()->where('status', ContactStatus::NEW->value)->count();
        $newPrevious = ContactMessage::query()->where('status', ContactStatus::NEW->value)->where('created_at', '<', $currentMonthStart)->count();

        $inProgressCurrent  = ContactMessage::query()->where('status', ContactStatus::IN_PROGRESS->value)->count();
        $inProgressPrevious = ContactMessage::query()->where('status', ContactStatus::IN_PROGRESS->value)->where('created_at', '<', $currentMonthStart)->count();

        $resolvedCurrent  = ContactMessage::query()->where('status', ContactStatus::RESOLVED->value)->count();
        $resolvedPrevious = ContactMessage::query()->where('status', ContactStatus::RESOLVED->value)->where('created_at', '<', $currentMonthStart)->count();

        $unassignedCurrent  = ContactMessage::query()->whereNull('assigned_to')->count();
        $unassignedPrevious = ContactMessage::query()->whereNull('assigned_to')->where('created_at', '<', $currentMonthStart)->count();

        return [
            $this->buildCard('Total', $totalCurrent, $totalPrevious, 'heroicon-o-inbox-stack', 'primary'),
            $this->buildCard('New', $newCurrent, $newPrevious, 'heroicon-o-envelope', 'info'),
            $this->buildCard('In Progress', $inProgressCurrent, $inProgressPrevious, 'heroicon-o-arrow-path', 'warning'),
            $this->buildCard('Resolved', $resolvedCurrent, $resolvedPrevious, 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Unassigned', $unassignedCurrent, $unassignedPrevious, 'heroicon-o-user-minus', 'danger'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(ContactMessage $enquiry): array
    {
        $enquiry->load('assignedTo');

        return [
            'id'             => $enquiry->id,
            'name'           => $enquiry->name,
            'company'        => $enquiry->company,
            'email'          => $enquiry->email,
            'phone'          => $enquiry->phone,
            'subject'        => $enquiry->subject,
            'enquiry_type'   => $enquiry->enquiry_type,
            'enquiry_type_label' => $enquiry->enquiry_type?->label() ?? '—',
            'message'        => $enquiry->message,
            'attachments'    => $enquiry->attachments ?? [],
            'status'         => $enquiry->status,
            'status_label'   => $enquiry->statusLabel(),
            'status_color'   => $enquiry->statusColor(),
            'priority'       => $enquiry->priority,
            'priority_label' => $enquiry->priorityLabel(),
            'priority_color' => $enquiry->priorityColor(),
            'source'         => $enquiry->source,
            'internal_notes' => $enquiry->internal_notes,
            'reply'          => $enquiry->reply,
            'replied_at'     => $enquiry->replied_at,
            'read_at'        => $enquiry->read_at,
            'created_at'     => $enquiry->created_at,
            'updated_at'     => $enquiry->updated_at,
            'assignee'       => $enquiry->assignedTo ? [
                'id'       => $enquiry->assignedTo->id,
                'name'     => $enquiry->assignedTo->name,
                'email'    => $enquiry->assignedTo->email,
                'initials' => $enquiry->assignedTo->initials(),
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        $base = ContactMessage::query();

        return [
            'statuses'     => ContactStatus::cases(),
            'enquiry_types' => ContactEnquiryType::cases(),
            'priorities'   => Priority::cases(),
            'sources'      => (clone $base)->whereNotNull('source')->where('source', '!=', '')->distinct()->orderBy('source')->pluck('source')->toArray(),
            'assignees'    => User::query()
                ->where(function (Builder $q): void {
                    $q->where('is_admin', true)
                        ->orWhereIn('id', ContactMessage::query()->whereNotNull('assigned_to')->distinct()->pluck('assigned_to'));
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
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryEnquiries($filters, 'created_at', 'desc')
            ->get()
            ->map(fn (ContactMessage $enquiry) => [
                'name'         => $enquiry->name,
                'company'      => $enquiry->company,
                'email'        => $enquiry->email,
                'phone'        => $enquiry->phone,
                'subject'      => $enquiry->subject,
                'enquiry_type' => $enquiry->enquiry_type?->label(),
                'status'       => $enquiry->statusLabel(),
                'priority'     => $enquiry->priorityLabel(),
                'source'       => $enquiry->source,
                'assigned_to'  => $enquiry->assignedTo?->name,
                'replied_at'   => $enquiry->replied_at?->format('Y-m-d H:i:s'),
                'created_at'   => $enquiry->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'name'        => $query->orderBy('name', $direction),
            'company'     => $query->orderBy('company', $direction),
            'email'       => $query->orderBy('email', $direction),
            'status'      => $query->orderBy('status', $direction),
            'priority'    => $query->orderBy('priority', $direction),
            'enquiry_type' => $query->orderBy('enquiry_type', $direction),
            'source'      => $query->orderBy('source', $direction),
            'updated_at'  => $query->orderBy('updated_at', $direction),
            'assigned_to' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'contact_messages.assigned_to')
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
            'value'    => '—',
            'label'    => 'No comparison available',
            'positive' => true,
        ];

        return [
            'label'           => $label,
            'value'           => number_format($current),
            'icon'            => $icon,
            'color'           => $color,
            'trend'           => $trend['value'],
            'trend_label'     => $hasComparison ? ($trend['label'].' vs last month') : 'No comparison available',
            'trend_positive'  => $trend['positive'],
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

        $change   = (($current - $previous) / $previous) * 100;
        $positive = $change >= 0;

        return [
            'value'    => sprintf('%s%.1f%%', $positive ? '+' : '', $change),
            'label'    => $positive ? 'Up' : 'Down',
            'positive' => $positive,
        ];
    }
}
