<?php

namespace App\Filament\Pages\Distributors;

use App\Models\DistributorBranch;
use App\Services\Admin\TerritoryAdminService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class TerritoriesPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Territories';

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.distributors.territories';

    protected static ?string $slug = 'distributors/territories';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'country')]
    public array $countryFilter = [];

    #[Url(as: 'district')]
    public array $districtFilter = [];

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'distributor')]
    public ?int $distributorFilter = null;

    #[Url(as: 'view')]
    public string $viewMode = 'table';

    #[Url(as: 'sort')]
    public string $sortField = 'name';

    #[Url(as: 'direction')]
    public string $sortDirection = 'asc';

    public int $perPage = 15;

    public bool $showFilterPanel = true;

    public bool $showDetailDrawer = false;

    public ?int $selectedBranchId = null;

    public function getTitle(): string
    {
        return 'Territories';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', DistributorBranch::class);

        if (! in_array($this->viewMode, ['table', 'map'], true)) {
            $this->viewMode = 'table';
        }
    }

    public function getTerritoryServiceProperty(): TerritoryAdminService
    {
        return app(TerritoryAdminService::class);
    }

    public function getBranchesProperty(): mixed
    {
        return $this->getTerritoryServiceProperty()
            ->paginateBranches($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getMappableBranchesProperty(): mixed
    {
        return $this->getTerritoryServiceProperty()->getMappableBranches($this->buildFilters());
    }

    public function getUnmappedCountProperty(): int
    {
        return $this->getTerritoryServiceProperty()->countUnmappedBranches($this->buildFilters());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getTerritoryServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getTerritoryServiceProperty()->getFilterOptions();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedBranchProperty(): ?array
    {
        if (empty($this->selectedBranchId)) {
            return null;
        }

        $branch = DistributorBranch::query()->find($this->selectedBranchId);

        if ($branch === null) {
            return null;
        }

        Gate::authorize('view', $branch);

        return $this->getTerritoryServiceProperty()->getDetail($branch);
    }

    public function canCreateBranch(): bool
    {
        return Gate::allows('create', DistributorBranch::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'country' => $this->countryFilter,
            'district' => $this->districtFilter,
            'status' => $this->statusFilter,
            'distributor_id' => $this->distributorFilter,
        ];
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['table', 'map'], true) ? $mode : 'table';
    }

    public function openDetailDrawer(int $id): void
    {
        $branch = DistributorBranch::query()->findOrFail($id);
        Gate::authorize('view', $branch);

        $this->selectedBranchId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedBranchId = null;
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

    public function toggleFilterPanel(): void
    {
        $this->showFilterPanel = ! $this->showFilterPanel;
    }

    public function applyFilters(): void
    {
        $this->resetPage();
        $this->showFilterPanel = true;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->countryFilter = [];
        $this->districtFilter = [];
        $this->statusFilter = [];
        $this->distributorFilter = null;
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function clearStatusFilter(): void
    {
        $this->statusFilter = [];
        $this->resetPage();
    }

    public function activeFilterCount(): int
    {
        $count = 0;

        if (filled($this->countryFilter)) {
            $count++;
        }
        if (filled($this->districtFilter)) {
            $count++;
        }
        if (filled($this->statusFilter)) {
            $count++;
        }
        if (filled($this->distributorFilter)) {
            $count++;
        }

        return $count;
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->countryFilter)
            || filled($this->districtFilter)
            || filled($this->statusFilter)
            || filled($this->distributorFilter);
    }

    public function export(string $format)
    {
        Gate::authorize('export', DistributorBranch::class);

        $allowed = ['csv', 'excel', 'pdf'];
        $format = strtolower($format);

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        return redirect()->to($this->getExportUrl($format));
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.distributors.territories.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'country' => $this->countryFilter ?: null,
            'district' => $this->districtFilter ?: null,
            'status' => $this->statusFilter ?: null,
            'distributor' => $this->distributorFilter,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCountryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDistrictFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDistributorFilter(): void
    {
        $this->resetPage();
    }
}
