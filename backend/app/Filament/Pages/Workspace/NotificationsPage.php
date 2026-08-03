<?php

namespace App\Filament\Pages\Workspace;

use App\Enums\NotificationCategory;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Services\Admin\NotificationService;
use Filament\Pages\Page;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class NotificationsPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.workspace.notifications';

    protected static ?string $slug = 'workspace/notifications';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'priority')]
    public array $priorityFilter = [];

    #[Url(as: 'category')]
    public array $categoryFilter = [];

    #[Url(as: 'type')]
    public array $typeFilter = [];

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $showDetailPanel = false;

    public ?string $selectedNotificationId = null;

    /**
     * @var array<int, string>
     */
    public array $selectedIds = [];

    public function getTitle(): string
    {
        return 'Notifications';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', DatabaseNotification::class);
    }

    public function getNotificationServiceProperty(): NotificationService
    {
        return app(NotificationService::class);
    }

    public function getNotificationsProperty(): mixed
    {
        return $this->getNotificationServiceProperty()
            ->paginateNotifications($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getNotificationServiceProperty()->getKpiCards();
    }

    public function getSelectedNotificationProperty(): ?array
    {
        if (empty($this->selectedNotificationId)) {
            return null;
        }

        $notification = Auth::user()->notifications()->find($this->selectedNotificationId);

        if ($notification === null) {
            return null;
        }

        Gate::authorize('view', $notification);

        return $this->getNotificationServiceProperty()->getNotificationDetails($notification);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'priority' => $this->priorityFilter,
            'category' => $this->categoryFilter,
            'type' => $this->typeFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailPanel(string $id): void
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        Gate::authorize('view', $notification);

        $this->selectedNotificationId = $id;
        $this->showDetailPanel = true;

        if ($notification->unread()) {
            $this->getNotificationServiceProperty()->markAsRead($notification);
        }
    }

    public function closeDetailPanel(): void
    {
        $this->showDetailPanel = false;
        $this->selectedNotificationId = null;
    }

    public function markAsRead(string $id): void
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        Gate::authorize('update', $notification);

        $this->getNotificationServiceProperty()->markAsRead($notification);
    }

    public function markAsUnread(string $id): void
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        Gate::authorize('update', $notification);

        $this->getNotificationServiceProperty()->markAsUnread($notification);
    }

    public function deleteNotification(string $id): void
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        Gate::authorize('delete', $notification);

        $this->getNotificationServiceProperty()->deleteSelected([$id]);

        if ($this->selectedNotificationId === $id) {
            $this->closeDetailPanel();
        }
    }

    public function markAllRead(): void
    {
        Gate::authorize('viewAny', DatabaseNotification::class);

        $this->getNotificationServiceProperty()->markAllRead();
    }

    public function bulkMarkRead(): void
    {
        Gate::authorize('viewAny', DatabaseNotification::class);

        $this->getNotificationServiceProperty()->markSelectedRead($this->selectedIds);
        $this->selectedIds = [];
    }

    public function bulkMarkUnread(): void
    {
        Gate::authorize('viewAny', DatabaseNotification::class);

        $this->getNotificationServiceProperty()->markSelectedUnread($this->selectedIds);
        $this->selectedIds = [];
    }

    public function bulkDelete(): void
    {
        Gate::authorize('viewAny', DatabaseNotification::class);

        $this->getNotificationServiceProperty()->deleteSelected($this->selectedIds);
        $this->selectedIds = [];
        $this->closeDetailPanel();
    }

    public function sortBy(string $field): void
    {
        $allowed = ['created_at', 'read_at', 'priority'];

        if (! in_array($field, $allowed, true)) {
            $field = 'created_at';
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->priorityFilter = [];
        $this->categoryFilter = [];
        $this->typeFilter = [];
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->selectedIds = [];
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateUntil(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->priorityFilter)
            || filled($this->categoryFilter)
            || filled($this->typeFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function toggleSelection(string $id): void
    {
        $key = array_search($id, $this->selectedIds, true);

        if ($key === false) {
            $this->selectedIds[] = $id;
        } else {
            unset($this->selectedIds[$key]);
            $this->selectedIds = array_values($this->selectedIds);
        }
    }

    public function selectPage(bool $selected): void
    {
        if ($selected) {
            $this->selectedIds = $this->getNotificationsProperty()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();
        } else {
            $this->selectedIds = [];
        }
    }
}
