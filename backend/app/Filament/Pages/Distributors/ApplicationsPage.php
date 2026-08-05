<?php

namespace App\Filament\Pages\Distributors;

use App\Enums\DistributorStatus;
use App\Models\DistributorRequest;
use App\Services\Admin\ApplicationAdminService;
use App\Services\DistributorOnboardingService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ApplicationsPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Applications';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.distributors.applications';

    protected static ?string $slug = 'distributors/applications';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'country')]
    public array $countryFilter = [];

    #[Url(as: 'region')]
    public array $regionFilter = [];

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

    /** @var array<int, int> */
    public array $selectedIds = [];

    public bool $showDetailDrawer = false;

    public ?int $selectedApplicationId = null;

    public function getTitle(): string
    {
        return 'Applications';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', DistributorRequest::class);
    }

    public function getApplicationServiceProperty(): ApplicationAdminService
    {
        return app(ApplicationAdminService::class);
    }

    public function getApplicationsProperty(): mixed
    {
        return $this->getApplicationServiceProperty()
            ->paginateApplications($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getApplicationServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedApplicationProperty(): ?array
    {
        if (empty($this->selectedApplicationId)) {
            return null;
        }

        $application = DistributorRequest::query()->find($this->selectedApplicationId);

        if ($application === null) {
            return null;
        }

        Gate::authorize('view', $application);

        return $this->getApplicationServiceProperty()->getDetail($application);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getApplicationServiceProperty()->getFilterOptions();
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
            'country' => $this->countryFilter,
            'region' => $this->regionFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $application = DistributorRequest::query()->findOrFail($id);
        Gate::authorize('view', $application);

        $this->selectedApplicationId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedApplicationId = null;
    }

    public function approve(int $id): void
    {
        $application = DistributorRequest::query()->findOrFail($id);
        Gate::authorize('update', $application);

        if ($application->status === DistributorStatus::APPROVED) {
            return;
        }

        app(DistributorOnboardingService::class)->approve($application, auth()->user());

        if ($this->selectedApplicationId === $id) {
            $this->closeDetailDrawer();
        }

        Notification::make()
            ->title('Application approved and distributor account created')
            ->success()
            ->send();
    }

    public function reject(int $id): void
    {
        $application = DistributorRequest::query()->findOrFail($id);
        Gate::authorize('update', $application);

        if ($application->status === DistributorStatus::REJECTED) {
            return;
        }

        app(DistributorOnboardingService::class)->reject($application, null, auth()->user());

        if ($this->selectedApplicationId === $id) {
            $this->closeDetailDrawer();
        }

        Notification::make()
            ->title('Application rejected')
            ->success()
            ->send();
    }

    public function bulkApprove(): void
    {
        if (empty($this->selectedIds)) {
            return;
        }

        $applications = DistributorRequest::query()->whereIn('id', $this->selectedIds)->get();
        $service = app(DistributorOnboardingService::class);
        $count = 0;

        foreach ($applications as $application) {
            Gate::authorize('update', $application);

            if ($application->status !== DistributorStatus::APPROVED) {
                $service->approve($application, auth()->user());
                $count++;
            }
        }

        $this->selectedIds = [];

        Notification::make()
            ->title("Approved {$count} application(s) and created distributor accounts")
            ->success()
            ->send();
    }

    public function bulkReject(): void
    {
        if (empty($this->selectedIds)) {
            return;
        }

        $applications = DistributorRequest::query()->whereIn('id', $this->selectedIds)->get();
        $service = app(DistributorOnboardingService::class);
        $count = 0;

        foreach ($applications as $application) {
            Gate::authorize('update', $application);

            if ($application->status !== DistributorStatus::REJECTED) {
                $service->reject($application, null, auth()->user());
                $count++;
            }
        }

        $this->selectedIds = [];

        Notification::make()
            ->title("Rejected {$count} application(s)")
            ->success()
            ->send();
    }

    public function toggleSelectAll(): void
    {
        $pageIds = $this->getApplicationsProperty()->pluck('id')->map(fn ($id) => (int) $id)->all();

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
        $this->countryFilter = [];
        $this->regionFilter = [];
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

    public function updatedCountryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRegionFilter(): void
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
            || filled($this->countryFilter)
            || filled($this->regionFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.distributors.applications.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'priority' => $this->priorityFilter ?: null,
            'country' => $this->countryFilter ?: null,
            'region' => $this->regionFilter ?: null,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}
