<?php

namespace App\Filament\Pages\CustomerSuccess;

use App\Enums\FeedbackStatus;
use App\Models\CustomerFeedback;
use App\Services\Admin\FeedbackAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class FeedbackPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Customer Success';

    protected static ?string $navigationLabel = 'Feedback';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.customer-success.feedback';

    protected static ?string $slug = 'customer-success/feedback';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'category')]
    public array $categoryFilter = [];

    #[Url(as: 'priority')]
    public array $priorityFilter = [];

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedFeedbackId = null;

    public function getTitle(): string
    {
        return 'Feedback';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', CustomerFeedback::class);
    }

    public function getFeedbackServiceProperty(): FeedbackAdminService
    {
        return app(FeedbackAdminService::class);
    }

    public function getFeedbackProperty(): mixed
    {
        return $this->getFeedbackServiceProperty()
            ->paginateFeedback($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getFeedbackServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedFeedbackProperty(): ?array
    {
        if (empty($this->selectedFeedbackId)) {
            return null;
        }

        $feedback = CustomerFeedback::query()->find($this->selectedFeedbackId);

        if ($feedback === null) {
            return null;
        }

        Gate::authorize('view', $feedback);

        return $this->getFeedbackServiceProperty()->getDetail($feedback);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'category' => $this->categoryFilter,
            'priority' => $this->priorityFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $feedback = CustomerFeedback::query()->findOrFail($id);
        Gate::authorize('view', $feedback);

        $this->selectedFeedbackId = $id;
        $this->showDetailDrawer = true;

        if (! $feedback->isRead()) {
            $feedback->markAsRead();
        }
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedFeedbackId = null;
    }

    public function markRead(int $id): void
    {
        $feedback = CustomerFeedback::query()->findOrFail($id);
        Gate::authorize('update', $feedback);

        $feedback->markAsRead();

        Notification::make()->title('Marked as read')->success()->send();
    }

    public function markUnread(int $id): void
    {
        $feedback = CustomerFeedback::query()->findOrFail($id);
        Gate::authorize('update', $feedback);

        $feedback->markAsUnread();

        Notification::make()->title('Marked as unread')->success()->send();
    }

    public function markInProgress(int $id): void
    {
        $feedback = CustomerFeedback::query()->findOrFail($id);
        Gate::authorize('update', $feedback);

        $feedback->forceFill(['status' => FeedbackStatus::IN_PROGRESS->value])->save();

        Notification::make()->title('Status updated to In Progress')->success()->send();
    }

    public function markResolved(int $id): void
    {
        $feedback = CustomerFeedback::query()->findOrFail($id);
        Gate::authorize('update', $feedback);

        $feedback->forceFill(['status' => FeedbackStatus::RESOLVED->value])->save();

        Notification::make()->title('Feedback resolved')->success()->send();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = [];
        $this->categoryFilter = [];
        $this->priorityFilter = [];
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->categoryFilter)
            || filled($this->priorityFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
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

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.customer-success.feedback.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'category' => $this->categoryFilter ?: null,
            'priority' => $this->priorityFilter ?: null,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}
