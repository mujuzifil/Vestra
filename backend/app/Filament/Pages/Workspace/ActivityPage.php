<?php

namespace App\Filament\Pages\Workspace;

use App\Enums\ActivityCategory;
use App\Enums\ActivityStatus;
use App\Models\AuditLog;
use App\Services\Admin\ActivityService;
use App\Services\ReportExportService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ActivityPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Activity';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.workspace.activity';

    protected static ?string $slug = 'workspace/activity';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'category')]
    public array $categoryFilter = [];

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'module')]
    public array $moduleFilter = [];

    #[Url(as: 'user')]
    public ?int $userFilter = null;

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 20;

    public bool $showDetailPanel = false;

    public ?string $selectedActivityId = null;

    /**
     * @var array<int, string>
     */
    public array $selectedIds = [];

    public function getTitle(): string
    {
        return 'Activity';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', AuditLog::class);
    }

    public function getActivityServiceProperty(): ActivityService
    {
        return app(ActivityService::class);
    }

    public function getExportServiceProperty(): ReportExportService
    {
        return app(ReportExportService::class);
    }

    public function getActivitiesProperty(): mixed
    {
        return $this->getActivityServiceProperty()
            ->getActivities($this->buildFilters(), $this->perPage, $this->getPage());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getActivityServiceProperty()->getKpiCards($this->buildFilters());
    }

    public function getSelectedActivityProperty(): ?array
    {
        if (empty($this->selectedActivityId)) {
            return null;
        }

        $activity = $this->getActivityServiceProperty()->findActivity($this->selectedActivityId);

        if ($activity === null) {
            return null;
        }

        $this->authorizeActivity($activity);

        return $activity;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getActivityServiceProperty()->getFilterOptions();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'category' => $this->categoryFilter,
            'status' => $this->statusFilter,
            'module' => $this->moduleFilter,
            'user' => $this->userFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailPanel(string $id): void
    {
        $activity = $this->getActivityServiceProperty()->findActivity($id);

        if ($activity === null) {
            return;
        }

        $this->authorizeActivity($activity);

        $this->selectedActivityId = $id;
        $this->showDetailPanel = true;
    }

    public function closeDetailPanel(): void
    {
        $this->showDetailPanel = false;
        $this->selectedActivityId = null;
    }

    public function export(string $format)
    {
        Gate::authorize('export', AuditLog::class);

        $allowed = ['csv', 'excel', 'pdf'];
        $format = strtolower($format);

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $rows = $this->getActivityServiceProperty()->forExport($this->buildFilters());
        $columns = $this->getExportColumns();
        $filename = 'activity-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $this->getExportServiceProperty()->csv($filename, $columns, $rows),
            'excel' => $this->getExportServiceProperty()->excel($filename, $columns, $rows),
            'pdf' => $this->getExportServiceProperty()->pdf($filename, 'Activity Export', $columns, $rows, $this->getFilterPeriodLabel()),
            default => abort(400, 'Unsupported export format.'),
        };
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = [];
        $this->statusFilter = [];
        $this->moduleFilter = [];
        $this->userFilter = null;
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->selectedIds = [];
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedModuleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedUserFilter(): void
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
            || filled($this->categoryFilter)
            || filled($this->statusFilter)
            || filled($this->moduleFilter)
            || filled($this->userFilter)
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
            $this->selectedIds = $this->getActivitiesProperty()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();
        } else {
            $this->selectedIds = [];
        }
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    protected function getExportColumns(): array
    {
        return [
            ['name' => 'date', 'label' => 'Date'],
            ['name' => 'activity', 'label' => 'Activity'],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'module', 'label' => 'Module'],
            ['name' => 'user', 'label' => 'User'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'ip_address', 'label' => 'IP Address'],
            ['name' => 'user_agent', 'label' => 'User Agent'],
            ['name' => 'related_entity', 'label' => 'Related Entity'],
        ];
    }

    protected function getFilterPeriodLabel(): ?string
    {
        $start = $this->dateFrom ? Carbon\Carbon::parse($this->dateFrom)->format('M d, Y') : null;
        $end = $this->dateUntil ? Carbon\Carbon::parse($this->dateUntil)->format('M d, Y') : null;

        if ($start && $end) {
            return "{$start} - {$end}";
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    private function authorizeActivity(array $activity): void
    {
        if ($activity['source'] === 'audit_log') {
            $log = AuditLog::query()->find((int) str_replace('audit-', '', $activity['id']));

            if ($log !== null) {
                Gate::authorize('view', $log);
            }

            return;
        }

        Gate::authorize('viewAny', AuditLog::class);
    }
}
