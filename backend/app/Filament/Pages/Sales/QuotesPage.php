<?php

namespace App\Filament\Pages\Sales;

use App\Enums\QuoteRequestPriority;
use App\Enums\QuoteRequestStatus;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\Admin\QuoteAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class QuotesPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Quotes';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.sales.quotes';

    protected static ?string $slug = 'sales/quotes';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'priority')]
    public array $priorityFilter = [];

    #[Url(as: 'district')]
    public array $districtFilter = [];

    #[Url(as: 'city')]
    public array $cityFilter = [];

    #[Url(as: 'assigned_to')]
    public ?int $assignedToFilter = null;

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'close_from')]
    public ?string $closeFrom = null;

    #[Url(as: 'close_until')]
    public ?string $closeUntil = null;

    #[Url(as: 'min_value')]
    public ?string $minValue = null;

    #[Url(as: 'max_value')]
    public ?string $maxValue = null;

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    /** @var array<int, int> */
    public array $selectedQuoteIds = [];

    public bool $showDetailDrawer = false;

    public ?int $selectedQuoteId = null;

    public bool $showFormDrawer = false;

    public ?int $editingQuoteId = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [
        'status' => '',
        'priority' => '',
        'estimated_value' => '',
        'expected_close_date' => '',
        'assigned_to' => null,
        'admin_notes' => '',
        'requirements' => '',
    ];

    public function getTitle(): string
    {
        return 'Quotes';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', QuoteRequest::class);
    }

    public function getQuoteServiceProperty(): QuoteAdminService
    {
        return app(QuoteAdminService::class);
    }

    public function getQuotesProperty(): mixed
    {
        return $this->getQuoteServiceProperty()
            ->paginateQuotes($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getQuoteServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedQuoteProperty(): ?array
    {
        if (empty($this->selectedQuoteId)) {
            return null;
        }

        $quote = QuoteRequest::query()->find($this->selectedQuoteId);

        if ($quote === null) {
            return null;
        }

        Gate::authorize('view', $quote);

        return $this->getQuoteServiceProperty()->getQuoteDetail($quote);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getQuoteServiceProperty()->getFilterOptions();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAssigneesProperty(): array
    {
        return User::query()
            ->where(function ($q): void {
                $q->where('is_admin', true)->orWhereHas('roles');
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name, 'initials' => $user->initials()])
            ->toArray();
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
            'district' => $this->districtFilter,
            'city' => $this->cityFilter,
            'assigned_to' => $this->assignedToFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
            'close_from' => $this->closeFrom,
            'close_until' => $this->closeUntil,
            'min_value' => $this->minValue,
            'max_value' => $this->maxValue,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $quote = QuoteRequest::query()->findOrFail($id);
        Gate::authorize('view', $quote);

        $this->selectedQuoteId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedQuoteId = null;
    }

    public function openEditDrawer(int $id): void
    {
        $quote = QuoteRequest::query()->findOrFail($id);
        Gate::authorize('update', $quote);

        $this->editingQuoteId = $quote->id;
        $this->form = [
            'status' => $quote->status?->value ?? QuoteRequestStatus::PENDING->value,
            'priority' => $quote->priority ?? QuoteRequestPriority::MEDIUM->value,
            'estimated_value' => $quote->estimated_value !== null ? (string) $quote->estimated_value : '',
            'expected_close_date' => $quote->expected_close_date?->format('Y-m-d') ?? '',
            'assigned_to' => $quote->assigned_to,
            'admin_notes' => $quote->admin_notes ?? '',
            'requirements' => $quote->requirements ?? '',
        ];
        $this->showFormDrawer = true;
    }

    public function closeFormDrawer(): void
    {
        $this->showFormDrawer = false;
        $this->editingQuoteId = null;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->form = [
            'status' => QuoteRequestStatus::PENDING->value,
            'priority' => QuoteRequestPriority::MEDIUM->value,
            'estimated_value' => '',
            'expected_close_date' => '',
            'assigned_to' => null,
            'admin_notes' => '',
            'requirements' => '',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'form.status' => ['required', 'string', 'in:'.implode(',', array_map(fn ($s) => $s->value, QuoteRequestStatus::cases()))],
            'form.priority' => ['nullable', 'string', 'in:'.implode(',', array_map(fn ($p) => $p->value, QuoteRequestPriority::cases()))],
            'form.estimated_value' => ['nullable', 'numeric', 'min:0'],
            'form.expected_close_date' => ['nullable', 'date'],
            'form.assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'form.admin_notes' => ['nullable', 'string'],
            'form.requirements' => ['nullable', 'string'],
        ];
    }

    public function saveQuote(): void
    {
        $this->validate();

        if (! $this->editingQuoteId) {
            return;
        }

        $quote = QuoteRequest::query()->findOrFail($this->editingQuoteId);
        Gate::authorize('update', $quote);

        $data = $this->form;
        $data['estimated_value'] = $data['estimated_value'] !== '' ? $data['estimated_value'] : null;
        $data['expected_close_date'] = $data['expected_close_date'] !== '' ? $data['expected_close_date'] : null;
        $data['assigned_to'] = $data['assigned_to'] ?: null;

        $this->getQuoteServiceProperty()->updateQuote($quote, $data);

        $this->closeFormDrawer();
        $this->resetPage();

        Notification::make()
            ->title('Quote updated')
            ->success()
            ->send();
    }

    public function updateStatus(int $id, string $status): void
    {
        $quote = QuoteRequest::query()->findOrFail($id);
        Gate::authorize('update', $quote);

        $statusEnum = QuoteRequestStatus::tryFrom($status);

        if ($statusEnum === null) {
            Notification::make()
                ->title('Invalid status')
                ->danger()
                ->send();

            return;
        }

        $this->getQuoteServiceProperty()->updateStatus($quote, $statusEnum);

        Notification::make()
            ->title('Status updated to '.$statusEnum->label())
            ->success()
            ->send();
    }

    public function bulkUpdateStatus(string $status): void
    {
        $statusEnum = QuoteRequestStatus::tryFrom($status);

        if ($statusEnum === null || empty($this->selectedQuoteIds)) {
            return;
        }

        $quotes = QuoteRequest::query()->whereIn('id', $this->selectedQuoteIds)->get();

        foreach ($quotes as $quote) {
            Gate::authorize('update', $quote);
            $this->getQuoteServiceProperty()->updateStatus($quote, $statusEnum);
        }

        $this->selectedQuoteIds = [];

        Notification::make()
            ->title('Updated '.$quotes->count().' quotes to '.$statusEnum->label())
            ->success()
            ->send();
    }

    public function toggleSelectAll(): void
    {
        $pageIds = $this->getQuotesProperty()->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count(array_intersect($this->selectedQuoteIds, $pageIds)) === count($pageIds) && count($pageIds) > 0) {
            $this->selectedQuoteIds = array_values(array_diff($this->selectedQuoteIds, $pageIds));
        } else {
            $this->selectedQuoteIds = array_values(array_unique(array_merge($this->selectedQuoteIds, $pageIds)));
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
        $this->districtFilter = [];
        $this->cityFilter = [];
        $this->assignedToFilter = null;
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->closeFrom = null;
        $this->closeUntil = null;
        $this->minValue = null;
        $this->maxValue = null;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->selectedQuoteIds = [];
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

    public function updatedDistrictFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssignedToFilter(): void
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

    public function updatedCloseFrom(): void
    {
        $this->resetPage();
    }

    public function updatedCloseUntil(): void
    {
        $this->resetPage();
    }

    public function updatedMinValue(): void
    {
        $this->resetPage();
    }

    public function updatedMaxValue(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->priorityFilter)
            || filled($this->districtFilter)
            || filled($this->cityFilter)
            || filled($this->assignedToFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil)
            || filled($this->closeFrom)
            || filled($this->closeUntil)
            || filled($this->minValue)
            || filled($this->maxValue);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.sales.quotes.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'priority' => $this->priorityFilter ?: null,
            'district' => $this->districtFilter ?: null,
            'city' => $this->cityFilter ?: null,
            'assigned_to' => $this->assignedToFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
            'close_from' => $this->closeFrom,
            'close_until' => $this->closeUntil,
            'min_value' => $this->minValue,
            'max_value' => $this->maxValue,
        ]);
    }
}
