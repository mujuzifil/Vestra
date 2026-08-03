<?php

namespace App\Services\Admin;

use App\Enums\ActivityCategory;
use App\Enums\ActivityStatus;
use App\Models\AuditLog;
use App\Models\LoginActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class ActivityService
{
    public const PER_PAGE = 20;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getActivities(array $filters = [], int $perPage = self::PER_PAGE, int $page = 1): LengthAwarePaginator
    {
        $collection = $this->filteredCollection($filters);
        $total = $collection->count();

        $items = $total > 0
            ? $collection->slice(($page - 1) * $perPage, $perPage)->values()
            : collect();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(array $filters = []): array
    {
        $collection = $this->filteredCollection($filters);

        $total = $collection->count();
        $userActivities = $collection->filter(fn (array $item): bool => ! empty($item['user']['id']))->count();
        $securityEvents = $collection->filter(fn (array $item): bool => $item['category'] === ActivityCategory::SECURITY)->count();
        $moduleActivities = $collection->filter(fn (array $item): bool => ! empty($item['subject']['type']))->count();
        $systemEvents = $collection->filter(fn (array $item): bool => empty($item['user']['id']))->count();

        return [
            [
                'title' => 'Total Activities',
                'value' => number_format($total),
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'primary',
                'subtitle' => 'All system activities',
            ],
            [
                'title' => 'User Activities',
                'value' => number_format($userActivities),
                'icon' => 'heroicon-o-users',
                'color' => 'success',
                'subtitle' => 'By all users',
            ],
            [
                'title' => 'Security Events',
                'value' => number_format($securityEvents),
                'icon' => 'heroicon-o-shield-exclamation',
                'color' => 'danger',
                'subtitle' => 'Security related events',
            ],
            [
                'title' => 'Module Activities',
                'value' => number_format($moduleActivities),
                'icon' => 'heroicon-o-squares-2x2',
                'color' => 'info',
                'subtitle' => 'Across all modules',
            ],
            [
                'title' => 'System Events',
                'value' => number_format($systemEvents),
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'warning',
                'subtitle' => 'Automated or unauthenticated',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function forExport(array $filters = []): array
    {
        return $this->filteredCollection($filters)
            ->map(fn (array $item): array => [
                'date' => $item['created_at']?->format('Y-m-d H:i:s'),
                'activity' => $item['title'],
                'category' => $item['category']->label(),
                'module' => $item['module'],
                'user' => $item['user']['name'] ?? 'System',
                'status' => $item['status']->label(),
                'ip_address' => $item['ip_address'] ?? '',
                'user_agent' => $item['user_agent'] ?? '',
                'related_entity' => $item['subject']['label'] ?? '',
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        $modules = AuditLog::query()
            ->whereNotNull('subject_type')
            ->distinct()
            ->pluck('subject_type')
            ->map(fn (string $type): string => class_basename($type))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $modules[] = 'Authentication';

        $users = User::query()
            ->where(function (Builder $query): void {
                $query->whereHas('auditLogs')
                    ->orWhereHas('loginActivities');
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->all();

        return [
            'modules' => $modules,
            'users' => $users,
        ];
    }

    public function findActivity(string $compositeId): ?array
    {
        if (str_starts_with($compositeId, 'audit-')) {
            $id = (int) substr($compositeId, strlen('audit-'));
            $log = AuditLog::query()->with(['user', 'subject'])->find($id);

            return $log ? $this->normalizeAuditLog($log) : null;
        }

        if (str_starts_with($compositeId, 'login-')) {
            $id = (int) substr($compositeId, strlen('login-'));
            $activity = LoginActivity::query()->with('user')->find($id);

            return $activity ? $this->normalizeLoginActivity($activity) : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filteredCollection(array $filters): Collection
    {
        $auditLogs = $this->fetchAuditLogs($filters);
        $loginActivities = $this->fetchLoginActivities($filters);

        $merged = collect($auditLogs->map(fn (AuditLog $log): array => $this->normalizeAuditLog($log)))
            ->merge(collect($loginActivities->map(fn (LoginActivity $activity): array => $this->normalizeLoginActivity($activity))));

        $merged = $merged->sortByDesc('created_at')->values();

        return $this->applyPostFilters($merged, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function fetchAuditLogs(array $filters): EloquentCollection
    {
        return AuditLog::query()
            ->with(['user', 'subject'])
            ->when($filters['user'] ?? null, fn (Builder $q, int $userId): Builder => $q->where('user_id', $userId))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date))
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $search = mb_strtolower($search);
                $q->where(function (Builder $sub) use ($search): void {
                    $sub->whereRaw('LOWER(action) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('CAST(subject_id AS TEXT) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('user', fn (Builder $userQ): Builder => $userQ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]));
                });
            })
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function fetchLoginActivities(array $filters): EloquentCollection
    {
        return LoginActivity::query()
            ->with('user')
            ->when($filters['user'] ?? null, fn (Builder $q, int $userId): Builder => $q->where('user_id', $userId))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date))
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $search = mb_strtolower($search);
                $q->where(function (Builder $sub) use ($search): void {
                    $sub->whereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(ip_address) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('user', fn (Builder $userQ): Builder => $userQ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]));
                });
            })
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $collection
     * @param  array<string, mixed>  $filters
     */
    private function applyPostFilters(Collection $collection, array $filters): Collection
    {
        $categories = array_filter((array) ($filters['category'] ?? []));
        if (! empty($categories)) {
            $collection = $collection->filter(fn (array $item): bool => in_array($item['category']->value, $categories, true));
        }

        $statuses = array_filter((array) ($filters['status'] ?? []));
        if (! empty($statuses)) {
            $collection = $collection->filter(fn (array $item): bool => in_array($item['status']->value, $statuses, true));
        }

        $modules = array_filter((array) ($filters['module'] ?? []));
        if (! empty($modules)) {
            $collection = $collection->filter(fn (array $item): bool => in_array($item['module'], $modules, true));
        }

        return $collection->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeAuditLog(AuditLog $log): array
    {
        $category = $this->auditCategory($log);
        $status = $this->auditStatus($log);

        return [
            'id' => "audit-{$log->id}",
            'source' => 'audit_log',
            'title' => $this->auditTitle($log),
            'description' => $this->auditDescription($log),
            'category' => $category,
            'status' => $status,
            'icon' => $this->auditIcon($log),
            'color' => $status->color(),
            'user' => $this->normalizeUser($log->user),
            'subject' => $this->normalizeSubject($log),
            'module' => $this->auditModule($log),
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'device' => null,
            'browser' => null,
            'os' => null,
            'location' => null,
            'metadata' => $log->details ?? [],
            'created_at' => $log->created_at,
            'updated_at' => $log->updated_at,
            'diff_for_humans' => $log->created_at?->diffForHumans() ?? '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeLoginActivity(LoginActivity $activity): array
    {
        $successful = (bool) $activity->successful;
        $category = $successful ? ActivityCategory::AUTHENTICATION : ActivityCategory::SECURITY;
        $status = $successful ? ActivityStatus::SUCCESS : ActivityStatus::ERROR;

        $title = $successful ? 'User login' : 'Failed login attempt';
        $description = $successful
            ? ($activity->user?->name ?? $activity->email ?? 'A user').' logged in successfully.'
            : 'Failed login attempt detected'.($activity->ip_address ? " from IP {$activity->ip_address}" : '').'.';

        return [
            'id' => "login-{$activity->id}",
            'source' => 'login_activity',
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'status' => $status,
            'icon' => $successful ? 'heroicon-o-arrow-right-end-on-rectangle' : 'heroicon-o-shield-exclamation',
            'color' => $status->color(),
            'user' => $this->normalizeUser($activity->user),
            'subject' => null,
            'module' => 'Authentication',
            'ip_address' => $activity->ip_address,
            'user_agent' => $activity->user_agent,
            'device' => $activity->device,
            'browser' => $activity->browser,
            'os' => $activity->os,
            'location' => $activity->location,
            'metadata' => [
                'successful' => $successful,
                'failed_reason' => $activity->failed_reason,
                'email' => $activity->email,
            ],
            'created_at' => $activity->created_at,
            'updated_at' => $activity->updated_at,
            'diff_for_humans' => $activity->created_at?->diffForHumans() ?? '',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeUser(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatarUrl(),
            'initials' => $user->initials(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeSubject(AuditLog $log): ?array
    {
        if (empty($log->subject_type)) {
            return null;
        }

        $label = $this->subjectLabel($log);

        return [
            'type' => class_basename($log->subject_type),
            'id' => $log->subject_id,
            'label' => $label,
            'url' => $this->subjectUrl($log),
        ];
    }

    private function auditCategory(AuditLog $log): ActivityCategory
    {
        $action = strtolower((string) $log->action);

        return match (true) {
            str_contains($action, 'failed')
                || str_contains($action, 'unauthorized')
                || str_contains($action, 'bypass')
                || str_contains($action, 'locked')
                || str_contains($action, 'security') => ActivityCategory::SECURITY,
            str_contains($action, 'login')
                || str_contains($action, 'logout')
                || str_contains($action, 'password')
                || str_contains($action, 'authenticated') => ActivityCategory::AUTHENTICATION,
            str_contains($action, 'quote')
                || str_contains($action, 'order')
                || str_contains($action, 'sale') => ActivityCategory::SALES,
            str_contains($action, 'contact')
                || str_contains($action, 'customer') => ActivityCategory::CRM,
            str_contains($action, 'distributor') => ActivityCategory::DISTRIBUTORS,
            str_contains($action, 'support')
                || str_contains($action, 'ticket') => ActivityCategory::SUPPORT,
            str_contains($action, 'product')
                || str_contains($action, 'inventory') => ActivityCategory::PRODUCTS,
            str_contains($action, 'blog')
                || str_contains($action, 'campaign')
                || str_contains($action, 'marketing') => ActivityCategory::MARKETING,
            str_contains($action, 'user')
                || str_contains($action, 'role')
                || str_contains($action, 'permission')
                || str_contains($action, 'setting') => ActivityCategory::ADMINISTRATION,
            default => ActivityCategory::SYSTEM,
        };
    }

    private function auditStatus(AuditLog $log): ActivityStatus
    {
        $action = strtolower((string) $log->action);

        return match (true) {
            str_contains($action, 'failed')
                || str_contains($action, 'error')
                || str_contains($action, 'denied')
                || str_contains($action, 'unauthorized')
                || str_contains($action, 'exception')
                || str_contains($action, 'bypass') => ActivityStatus::ERROR,
            str_contains($action, 'warning')
                || str_contains($action, 'deleted')
                || str_contains($action, 'removed')
                || str_contains($action, 'rejected') => ActivityStatus::WARNING,
            str_contains($action, 'created')
                || str_contains($action, 'submitted')
                || str_contains($action, 'success')
                || str_contains($action, 'approved')
                || str_contains($action, 'completed')
                || str_contains($action, 'login') => ActivityStatus::SUCCESS,
            default => ActivityStatus::INFORMATION,
        };
    }

    private function auditModule(AuditLog $log): string
    {
        if (! empty($log->subject_type)) {
            return class_basename($log->subject_type);
        }

        $category = $this->auditCategory($log);

        return $category->label();
    }

    private function auditIcon(AuditLog $log): string
    {
        $action = strtolower((string) $log->action);

        return match (true) {
            str_contains($action, 'quote') => 'heroicon-o-document-text',
            str_contains($action, 'distributor') => 'heroicon-o-user-group',
            str_contains($action, 'contact') => 'heroicon-o-envelope',
            str_contains($action, 'customer') => 'heroicon-o-users',
            str_contains($action, 'support') || str_contains($action, 'ticket') => 'heroicon-o-ticket',
            str_contains($action, 'blog') => 'heroicon-o-newspaper',
            str_contains($action, 'product') => 'heroicon-o-cube',
            str_contains($action, 'order') => 'heroicon-o-shopping-cart',
            str_contains($action, 'setting') => 'heroicon-o-cog-6-tooth',
            str_contains($action, 'task') => 'heroicon-o-check-circle',
            str_contains($action, 'login') || str_contains($action, 'logout') => 'heroicon-o-arrow-right-end-on-rectangle',
            str_contains($action, 'security') || str_contains($action, 'failed') => 'heroicon-o-shield-exclamation',
            default => 'heroicon-o-bolt',
        };
    }

    private function auditTitle(AuditLog $log): string
    {
        $action = $log->action;
        $subject = $this->subjectLabel($log) ?? ('#'.$log->subject_id);

        return match (true) {
            str_contains($action, 'quote_request') => "Quote request from {$subject}",
            str_contains($action, 'distributor_request') => "Distributor application from {$subject}",
            str_contains($action, 'contact_message') => "Contact message from {$subject}",
            str_contains($action, 'customer') => "Customer record {$subject}",
            str_contains($action, 'support_ticket') => "Support ticket {$subject}",
            str_contains($action, 'blog_post') => "Blog post \"{$subject}\"",
            str_contains($action, 'product') => "Product {$subject}",
            str_contains($action, 'user') => "User {$subject}",
            str_contains($action, 'order') => "Order {$subject}",
            str_contains($action, 'task') => "Task {$subject}",
            str_contains($action, 'login') => 'User login',
            default => ucfirst(str_replace(['.', '_'], ' ', $action)),
        };
    }

    private function auditDescription(AuditLog $log): string
    {
        $actor = $log->user?->name ?? 'System';
        $action = ucfirst(str_replace(['.', '_'], ' ', $log->action));

        return "{$action} by {$actor}".($log->subject_id ? " • related record #{$log->subject_id}" : '');
    }

    private function subjectLabel(AuditLog $log): ?string
    {
        $subject = $log->subject;

        if ($subject === null) {
            return $log->subject_id ? (string) $log->subject_id : null;
        }

        return $subject->company_name
            ?? $subject->full_name
            ?? $subject->name
            ?? $subject->title
            ?? $subject->reference_number
            ?? ((string) $log->subject_id);
    }

    private function subjectUrl(AuditLog $log): ?string
    {
        if (empty($log->subject_type) || $log->subject === null) {
            return null;
        }

        $resource = match (class_basename($log->subject_type)) {
            'QuoteRequest' => 'quote-requests',
            'DistributorRequest' => 'distributor-requests',
            'ContactMessage' => 'contact-messages',
            'CustomerFeedback' => 'customer-feedback',
            'BlogPost' => 'blog-posts',
            'Product' => 'products',
            'User' => 'users',
            'SupportTicket' => 'support-tickets',
            'Order' => 'orders',
            'Task' => 'tasks',
            default => null,
        };

        return $resource ? url("/{$resource}/{$log->subject_id}") : null;
    }
}
