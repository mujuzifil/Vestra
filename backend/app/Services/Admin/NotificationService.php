<?php

namespace App\Services\Admin;

use App\Enums\NotificationCategory;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Supported sort fields and their database mapping.
     */
    private const SORT_FIELDS = [
        'created_at' => 'created_at',
        'read_at' => 'read_at',
        'priority' => 'data->priority',
    ];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateNotifications(
        array $filters = [],
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->baseQuery();

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * @return array<string, mixed>
     */
    public function getKpiCards(): array
    {
        $user = $this->currentUser();
        $base = $user->notifications();
        $unread = $user->unreadNotifications();

        $total = $base->count();
        $unreadCount = $unread->count();
        $mentionsCount = $this->mentionsCount($user);
        $alertsCount = $this->alertsCount($user);

        return [
            [
                'title' => 'Total Notifications',
                'value' => $total,
                'icon' => 'heroicon-o-bell',
                'color' => 'primary',
                'trend' => null,
                'subtitle' => 'All time',
            ],
            [
                'title' => 'Unread',
                'value' => $unreadCount,
                'icon' => 'heroicon-o-envelope',
                'color' => 'danger',
                'trend' => null,
                'subtitle' => 'Awaiting your attention',
            ],
            [
                'title' => 'Mentions',
                'value' => $mentionsCount,
                'icon' => 'heroicon-o-at-symbol',
                'color' => 'info',
                'trend' => null,
                'subtitle' => 'Directed at you',
            ],
            [
                'title' => 'System Alerts',
                'value' => $alertsCount,
                'icon' => 'heroicon-o-shield-exclamation',
                'color' => 'warning',
                'trend' => null,
                'subtitle' => 'Critical & warnings',
            ],
        ];
    }

    public function markAsRead(DatabaseNotification $notification): void
    {
        $notification->markAsRead();
    }

    public function markAsUnread(DatabaseNotification $notification): void
    {
        $notification->update(['read_at' => null]);
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function markSelectedRead(array $ids): int
    {
        return $this->baseQuery()
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function markSelectedUnread(array $ids): int
    {
        return $this->baseQuery()
            ->whereIn('id', $ids)
            ->whereNotNull('read_at')
            ->update(['read_at' => null]);
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function deleteSelected(array $ids): int
    {
        return $this->baseQuery()->whereIn('id', $ids)->delete();
    }

    public function markAllRead(): int
    {
        return $this->baseQuery()
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }

    public function getUnreadCount(): int
    {
        return $this->currentUser()->unreadNotifications()->count();
    }

    /**
     * @return array<string, mixed>
     */
    public function getNotificationDetails(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $type = NotificationType::tryFromString($data['type'] ?? null) ?? NotificationType::SYSTEM;
        $category = NotificationCategory::tryFromString($data['category'] ?? null) ?? $type->category();
        $priority = NotificationPriority::tryFromString($data['priority'] ?? null) ?? NotificationPriority::INFORMATION;

        $triggeredBy = null;
        if (! empty($data['triggered_by_user_id'])) {
            $triggeredBy = User::query()->find($data['triggered_by_user_id'])?->only(['id', 'name', 'email']);
        }

        $relatedEntity = null;
        if (! empty($data['related_type']) && ! empty($data['related_id'])) {
            $relatedEntity = [
                'type' => $data['related_type'],
                'id' => $data['related_id'],
                'label' => $this->relatedEntityLabel($data['related_type'], $data['related_id']),
            ];
        }

        return [
            'id' => $notification->id,
            'type' => $type,
            'category' => $category,
            'priority' => $priority,
            'title' => $data['title'] ?? $type->label(),
            'message' => $data['message'] ?? '',
            'action_url' => $data['action_url'] ?? null,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
            'updated_at' => $notification->updated_at,
            'triggered_by' => $triggeredBy,
            'related_entity' => $relatedEntity,
            'variables' => $data['variables'] ?? [],
        ];
    }

    private function baseQuery(): MorphMany
    {
        return $this->currentUser()
            ->notifications()
            ->with(['notifiable']);
    }

    /**
     * @param  MorphMany  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(MorphMany $query, array $filters): void
    {
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;
        $priorities = (array) ($filters['priority'] ?? []);
        $categories = (array) ($filters['category'] ?? []);
        $types = (array) ($filters['type'] ?? []);
        $dateFrom = $filters['date_from'] ?? null;
        $dateUntil = $filters['date_until'] ?? null;

        if (filled($search)) {
            $search = mb_strtolower((string) $search);
            $query->where(function (Builder $q) use ($search): void {
                $q->whereRaw('LOWER(COALESCE(JSON_EXTRACT(data, \'$.title\'), \'\')) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(COALESCE(JSON_EXTRACT(data, \'$.message\'), \'\')) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($status === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($status === 'unread') {
            $query->whereNull('read_at');
        }

        if (! empty($priorities)) {
            $query->whereIn('data->priority', $priorities);
        }

        if (! empty($categories)) {
            $query->whereIn('data->category', $categories);
        }

        if (! empty($types)) {
            $query->whereIn('data->type', $types);
        }

        if (filled($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (filled($dateUntil)) {
            $query->whereDate('created_at', '<=', $dateUntil);
        }
    }

    /**
     * @param  MorphMany  $query
     */
    private function applySorting(MorphMany $query, string $sortField, string $sortDirection): void
    {
        $direction = in_array(strtolower($sortDirection), ['asc', 'desc'], true)
            ? strtolower($sortDirection)
            : 'desc';

        $field = self::SORT_FIELDS[$sortField] ?? self::SORT_FIELDS['created_at'];

        if ($sortField === 'priority') {
            $query->orderByRaw("
                CASE data->priority
                    WHEN 'critical' THEN 4
                    WHEN 'warning' THEN 3
                    WHEN 'success' THEN 2
                    WHEN 'information' THEN 1
                    ELSE 0
                END {$direction}
            ");
            $query->orderBy('created_at', 'desc');

            return;
        }

        $query->orderBy($field, $direction);
    }

    private function mentionsCount(User $user): int
    {
        return $user->notifications()
            ->where(function (Builder $q): void {
                $q->whereRaw('LOWER(COALESCE(JSON_EXTRACT(data, \'$.title\'), \'\')) LIKE ?', ['%@%'])
                    ->orWhereRaw('LOWER(COALESCE(JSON_EXTRACT(data, \'$.message\'), \'\')) LIKE ?', ['%@%'])
                    ->orWhereNotNull('data->triggered_by_user_id');
            })
            ->count();
    }

    private function alertsCount(User $user): int
    {
        return $user->notifications()
            ->where(function (Builder $q): void {
                $q->whereIn('data->priority', [
                    NotificationPriority::CRITICAL->value,
                    NotificationPriority::WARNING->value,
                ])
                    ->orWhereIn('data->category', [
                        NotificationCategory::SECURITY->value,
                        NotificationCategory::SYSTEM->value,
                    ]);
            })
            ->count();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function relatedEntityLabel(string $relatedType, int $relatedId): ?array
    {
        $class = $this->resolveRelatedClass($relatedType);

        if ($class === null) {
            return null;
        }

        $model = $class::query()->find($relatedId);

        if ($model === null) {
            return null;
        }

        $label = $model->name ?? ($model->title ?? ($model->subject ?? $relatedType.' #'.$relatedId));

        return [
            'id' => $model->getKey(),
            'label' => $label,
        ];
    }

    /**
     * @return class-string|null
     */
    private function resolveRelatedClass(string $relatedType): ?string
    {
        $map = [
            'quote' => \App\Models\QuoteRequest::class,
            'distributor' => \App\Models\Distributor::class,
            'support_ticket' => \App\Models\SupportTicket::class,
            'user' => \App\Models\User::class,
            'blog_post' => \App\Models\BlogPost::class,
            'product' => \App\Models\Product::class,
            'task' => \App\Models\Task::class,
        ];

        return $map[$relatedType] ?? null;
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
