<?php

namespace App\Filament\Pages\Distributors;

use App\Models\CreditAccount;
use App\Services\Admin\CreditAdminService;
use App\Services\CreditService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class CreditPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Credit';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.distributors.credit';

    protected static ?string $slug = 'distributors/credit';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'country')]
    public array $countryFilter = [];

    #[Url(as: 'sort')]
    public string $sortField = 'updated_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedAccountId = null;

    public bool $showAdjustDrawer = false;

    public ?int $adjustingAccountId = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [
        'new_limit' => '',
        'reason' => '',
    ];

    public function getTitle(): string
    {
        return 'Credit';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(Gate::allows('viewAny', CreditAccount::class), 403);
    }

    public function getCreditServiceProperty(): CreditAdminService
    {
        return app(CreditAdminService::class);
    }

    public function getAccountsProperty(): mixed
    {
        return $this->getCreditServiceProperty()
            ->paginateAccounts($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getCreditServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getCreditServiceProperty()->getFilterOptions();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedAccountProperty(): ?array
    {
        if (empty($this->selectedAccountId)) {
            return null;
        }

        $account = CreditAccount::query()->find($this->selectedAccountId);

        if ($account === null) {
            return null;
        }

        Gate::authorize('view', $account);

        return $this->getCreditServiceProperty()->getDetail($account);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'country' => $this->countryFilter,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $account = CreditAccount::query()->findOrFail($id);
        Gate::authorize('view', $account);

        $this->selectedAccountId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedAccountId = null;
    }

    public function openAdjustDrawer(int $id): void
    {
        $account = CreditAccount::query()->findOrFail($id);
        Gate::authorize('updateLimit', $account);

        $this->adjustingAccountId = $account->id;
        $this->form = [
            'new_limit' => (string) $account->limit,
            'reason' => '',
        ];
        $this->showAdjustDrawer = true;
    }

    public function closeAdjustDrawer(): void
    {
        $this->showAdjustDrawer = false;
        $this->adjustingAccountId = null;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->form = [
            'new_limit' => '',
            'reason' => '',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'form.new_limit' => ['required', 'numeric', 'min:0'],
            'form.reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function saveLimit(): void
    {
        $this->validate();

        if (! $this->adjustingAccountId) {
            return;
        }

        $account = CreditAccount::query()->findOrFail($this->adjustingAccountId);
        Gate::authorize('updateLimit', $account);

        app(CreditService::class)->updateLimit(
            $account,
            (float) $this->form['new_limit'],
            $this->form['reason']
        );

        $this->closeAdjustDrawer();
        $this->resetPage();

        Notification::make()
            ->title('Credit limit updated')
            ->success()
            ->send();
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
        $this->countryFilter = [];
        $this->sortField = 'updated_at';
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

    public function updatedCountryFilter(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->countryFilter);
    }

    public function export(string $format)
    {
        Gate::authorize('export', CreditAccount::class);

        $allowed = ['csv', 'excel', 'pdf'];
        $format = strtolower($format);

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        return redirect()->to($this->getExportUrl($format));
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.distributors.credit.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'country' => $this->countryFilter ?: null,
        ]);
    }
}
