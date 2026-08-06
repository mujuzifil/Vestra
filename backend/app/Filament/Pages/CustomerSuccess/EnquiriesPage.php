<?php

namespace App\Filament\Pages\CustomerSuccess;

use App\Models\ContactMessage;
use App\Services\Admin\EnquiryAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class EnquiriesPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Customer Success';

    protected static ?string $navigationLabel = 'Enquiries';

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.customer-success.enquiries';

    protected static ?string $slug = 'customer-success/enquiries';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'source')]
    public array $sourceFilter = [];

    #[Url(as: 'enquiry_type')]
    public array $enquiryTypeFilter = [];

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

    public ?int $selectedEnquiryId = null;

    public string $replyDraft = '';

    public function getTitle(): string
    {
        return 'Enquiries';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(Gate::allows('viewAny', ContactMessage::class), 403);
    }

    public function getEnquiryServiceProperty(): EnquiryAdminService
    {
        return app(EnquiryAdminService::class);
    }

    public function getEnquiriesProperty(): mixed
    {
        return $this->getEnquiryServiceProperty()
            ->paginateEnquiries($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getEnquiryServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedEnquiryProperty(): ?array
    {
        if (empty($this->selectedEnquiryId)) {
            return null;
        }

        $enquiry = ContactMessage::query()->find($this->selectedEnquiryId);

        if ($enquiry === null) {
            return null;
        }

        Gate::authorize('view', $enquiry);

        return $this->getEnquiryServiceProperty()->getDetail($enquiry);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getEnquiryServiceProperty()->getFilterOptions();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'source' => $this->sourceFilter,
            'enquiry_type' => $this->enquiryTypeFilter,
            'priority' => $this->priorityFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $enquiry = ContactMessage::query()->findOrFail($id);
        Gate::authorize('view', $enquiry);

        $this->selectedEnquiryId = $id;
        $this->replyDraft = $enquiry->reply ?? '';
        $this->showDetailDrawer = true;

        if (! $enquiry->isRead()) {
            $enquiry->markAsRead();
        }
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedEnquiryId = null;
        $this->replyDraft = '';
    }

    public function saveReply(): void
    {
        if (empty($this->selectedEnquiryId)) {
            return;
        }

        $enquiry = ContactMessage::query()->findOrFail($this->selectedEnquiryId);
        Gate::authorize('update', $enquiry);

        $enquiry->update(['reply' => $this->replyDraft]);

        Notification::make()->title('Reply draft saved')->success()->send();
    }

    public function sendReply(): void
    {
        if (empty($this->selectedEnquiryId)) {
            return;
        }

        $enquiry = ContactMessage::query()->findOrFail($this->selectedEnquiryId);
        Gate::authorize('update', $enquiry);

        if (empty($this->replyDraft)) {
            Notification::make()->title('Reply is empty — nothing sent')->warning()->send();

            return;
        }

        $enquiry->update(['reply' => $this->replyDraft]);

        \Illuminate\Support\Facades\Mail::to($enquiry->email)
            ->send(new \App\Mail\ContactReplyMail($enquiry));

        $enquiry->update([
            'replied_at' => now(),
            'status' => \App\Enums\ContactStatus::RESOLVED,
        ]);

        Notification::make()->title('Reply sent successfully')->success()->send();

        $this->closeDetailDrawer();
    }

    public function updateStatus(int $id, string $status): void
    {
        $enquiry = ContactMessage::query()->findOrFail($id);
        Gate::authorize('update', $enquiry);

        $enquiry->update(['status' => $status]);

        Notification::make()->title('Status updated')->success()->send();
    }

    public function markResolved(int $id): void
    {
        $this->updateStatus($id, \App\Enums\ContactStatus::RESOLVED->value);
    }

    public function saveInternalNotes(int $id, string $notes): void
    {
        $enquiry = ContactMessage::query()->findOrFail($id);
        Gate::authorize('update', $enquiry);

        $enquiry->update(['internal_notes' => $notes]);

        Notification::make()->title('Internal notes saved')->success()->send();
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
        $this->sourceFilter = [];
        $this->enquiryTypeFilter = [];
        $this->priorityFilter = [];
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
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

    public function updatedSourceFilter(): void
    {
        $this->resetPage();
    }

    public function updatedEnquiryTypeFilter(): void
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

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->sourceFilter)
            || filled($this->enquiryTypeFilter)
            || filled($this->priorityFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.customer-success.enquiries.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'source' => $this->sourceFilter ?: null,
            'enquiry_type' => $this->enquiryTypeFilter ?: null,
            'priority' => $this->priorityFilter ?: null,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}
