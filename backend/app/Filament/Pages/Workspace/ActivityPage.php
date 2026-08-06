<?php

namespace App\Filament\Pages\Workspace;

use App\Enums\ActivityCategory;
use App\Enums\ActivityStatus;
use App\Models\AuditLog;
use App\Services\Admin\ActivityService;
use App\Services\ReportExportService;
use Carbon\Carbon;
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

    public int $perPage = 30;

    /**
     * @var array<int, string>
     */
    public array $selectedIds = [];

    public function getTitle(): string
    {
        return 'Activity';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(Gate::allows('viewAny', AuditLog::class), 403);
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
        $start = $this->dateFrom ? Carbon::parse($this->dateFrom)->format('M d, Y') : null;
        $end = $this->dateUntil ? Carbon::parse($this->dateUntil)->format('M d, Y') : null;

        if ($start && $end) {
            return "{$start} - {$end}";
        }

        return null;
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.workspace.activity.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'category' => $this->categoryFilter ?: null,
            'status' => $this->statusFilter ?: null,
            'module' => $this->moduleFilter ?: null,
            'user' => $this->userFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}
