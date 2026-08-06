<?php

namespace App\Filament\Pages\CustomerSuccess;

use App\Models\SupportTicket;
use App\Services\Admin\SupportAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class SupportPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Customer Success';

    protected static ?string $navigationLabel = 'Support';

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.customer-success.support';

    protected static ?string $slug = 'customer-success/support';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'priority')]
    public array $priorityFilter = [];

    #[Url(as: 'enquiry_type')]
    public array $enquiryTypeFilter = [];

    #[Url(as: 'assigned_to')]
    public ?int $assignedToFilter = null;

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    /** @var array<int, int> */
    public array $selectedIds = [];

    public bool $showDetailDrawer = false;

    public ?int $selectedTicketId = null;

    public string $replyMessage = '';

    public bool $replyIsInternal = false;

    public string $updateStatus = '';

    public function getTitle(): string
    {
        return 'Support';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(Gate::allows('viewAny', SupportTicket::class), 403);
    }

    public function getSupportServiceProperty(): SupportAdminService
    {
        return app(SupportAdminService::class);
    }

    public function getTicketsProperty(): mixed
    {
        return $this->getSupportServiceProperty()
            ->paginateTickets($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getSupportServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedTicketProperty(): ?array
    {
        if (empty($this->selectedTicketId)) {
            return null;
        }

        $ticket = SupportTicket::query()->find($this->selectedTicketId);

        if ($ticket === null) {
            return null;
        }

        Gate::authorize('view', $ticket);

        return $this->getSupportServiceProperty()->getDetail($ticket);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getSupportServiceProperty()->getFilterOptions();
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
            'enquiry_type' => $this->enquiryTypeFilter,
            'assigned_to' => $this->assignedToFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $ticket = SupportTicket::query()->findOrFail($id);
        Gate::authorize('view', $ticket);

        $this->selectedTicketId = $id;
        $this->showDetailDrawer = true;
        $this->replyMessage = '';
        $this->replyIsInternal = false;
        $this->updateStatus = $ticket->status;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedTicketId = null;
        $this->replyMessage = '';
        $this->replyIsInternal = false;
        $this->updateStatus = '';
    }

    public function submitReply(): void
    {
        if (empty(trim($this->replyMessage))) {
            return;
        }

        $ticket = SupportTicket::query()->findOrFail($this->selectedTicketId);
        Gate::authorize('reply', $ticket);

        app(\App\Services\SupportTicketService::class)->adminReply(
            $ticket,
            auth()->user(),
            [
                'message' => $this->replyMessage,
                'is_internal' => $this->replyIsInternal,
            ]
        );

        $this->replyMessage = '';
        $this->replyIsInternal = false;

        Notification::make()
            ->title('Reply sent')
            ->success()
            ->send();
    }

    public function updateTicketStatus(int $id): void
    {
        $ticket = SupportTicket::query()->findOrFail($id);
        Gate::authorize('update', $ticket);

        if (empty($this->updateStatus) || $this->updateStatus === $ticket->status) {
            return;
        }

        $data = ['status' => $this->updateStatus];

        if ($this->updateStatus === 'resolved' && $ticket->resolved_at === null) {
            $data['resolved_at'] = now();
        } elseif ($this->updateStatus !== 'resolved') {
            $data['resolved_at'] = null;
        }

        $ticket->update($data);

        Notification::make()
            ->title('Status updated to '.ucfirst(str_replace('_', ' ', $this->updateStatus)))
            ->success()
            ->send();
    }

    public function assignTicket(int $id, int $userId): void
    {
        $ticket = SupportTicket::query()->findOrFail($id);
        Gate::authorize('update', $ticket);

        $ticket->update(['assigned_to' => $userId]);

        Notification::make()
            ->title('Ticket assigned')
            ->success()
            ->send();
    }

    public function toggleSelectAll(): void
    {
        $pageIds = $this->getTicketsProperty()->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count(array_intersect($this->selectedIds, $pageIds)) === count($pageIds) && count($pageIds) > 0) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, $pageIds));
        } else {
            $this->selectedIds = array_values(array_unique(array_merge($this->selectedIds, $pageIds)));
        }
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
        $this->priorityFilter = [];
        $this->enquiryTypeFilter = [];
        $this->assignedToFilter = null;
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->selectedIds = [];
        $this->resetPage();
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function updatedPriorityFilter(): void { $this->resetPage(); }

    public function updatedEnquiryTypeFilter(): void { $this->resetPage(); }

    public function updatedAssignedToFilter(): void { $this->resetPage(); }

    public function updatedDateFrom(): void { $this->resetPage(); }

    public function updatedDateUntil(): void { $this->resetPage(); }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->priorityFilter)
            || filled($this->enquiryTypeFilter)
            || filled($this->assignedToFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.customer-success.support.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'priority' => $this->priorityFilter ?: null,
            'enquiry_type' => $this->enquiryTypeFilter ?: null,
            'assigned_to' => $this->assignedToFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}
