<?php

namespace App\Filament\Pages\Distributors;

use App\Enums\DistributorAccountStatus;
use App\Models\Distributor;
use App\Services\Admin\PartnerAdminService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ActivePartnersPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Active Partners';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.distributors.active-partners';

    protected static ?string $slug = 'distributors/active-partners';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'country')]
    public array $countryFilter = [];

    #[Url(as: 'region')]
    public array $regionFilter = [];

    #[Url(as: 'sales_rep')]
    public ?int $salesRepFilter = null;

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedPartnerId = null;

    public function getTitle(): string
    {
        return 'Active Partners';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', Distributor::class);
    }

    public function getPartnerServiceProperty(): PartnerAdminService
    {
        return app(PartnerAdminService::class);
    }

    public function getPartnersProperty(): mixed
    {
        return $this->getPartnerServiceProperty()
            ->paginatePartners($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getPartnerServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedPartnerProperty(): ?array
    {
        if (empty($this->selectedPartnerId)) {
            return null;
        }

        $distributor = Distributor::query()->find($this->selectedPartnerId);

        if ($distributor === null) {
            return null;
        }

        Gate::authorize('view', $distributor);

        return $this->getPartnerServiceProperty()->getDetail($distributor);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getPartnerServiceProperty()->getFilterOptions();
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
            'region' => $this->regionFilter,
            'sales_rep' => $this->salesRepFilter,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $distributor = Distributor::query()->findOrFail($id);
        Gate::authorize('view', $distributor);

        $this->selectedPartnerId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedPartnerId = null;
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
        $this->regionFilter = [];
        $this->salesRepFilter = null;
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

    public function updatedCountryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRegionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSalesRepFilter(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->countryFilter)
            || filled($this->regionFilter)
            || filled($this->salesRepFilter);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getStatusOptionsProperty(): array
    {
        return collect(DistributorAccountStatus::cases())
            ->map(fn (DistributorAccountStatus $status) => ['value' => $status->value, 'label' => $status->label()])
            ->toArray();
    }

    public function export(string $format)
    {
        Gate::authorize('export', Distributor::class);

        $allowed = ['csv', 'excel', 'pdf'];
        $format = strtolower($format);

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        return redirect()->to($this->getExportUrl($format));
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.distributors.active-partners.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'country' => $this->countryFilter ?: null,
            'region' => $this->regionFilter ?: null,
            'sales_rep' => $this->salesRepFilter,
        ]);
    }
}
