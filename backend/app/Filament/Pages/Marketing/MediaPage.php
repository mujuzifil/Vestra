<?php

namespace App\Filament\Pages\Marketing;

use App\Filament\Resources\BlogPostResource;
use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Services\Admin\MediaAdminService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class MediaPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Media';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.marketing.media';

    protected static ?string $slug = 'marketing/media';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'type')]
    public array $typeFilter = [];

    #[Url(as: 'source')]
    public array $sourceFilter = [];

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'view')]
    public string $viewMode = 'grid';

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 24;

    public bool $showDetailDrawer = false;

    public ?string $selectedMediaId = null;

    /** @var array<int, string> */
    public array $selectedIds = [];

    public function getTitle(): string
    {
        return 'Media';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', Product::class);

        if (! in_array($this->viewMode, ['grid', 'list'], true)) {
            $this->viewMode = 'grid';
        }
    }

    public function getMediaServiceProperty(): MediaAdminService
    {
        return app(MediaAdminService::class);
    }

    public function getMediaItemsProperty(): mixed
    {
        return $this->getMediaServiceProperty()
            ->paginate($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage, $this->getPage());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getMediaServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedMediaProperty(): ?array
    {
        if (empty($this->selectedMediaId)) {
            return null;
        }

        return $this->getMediaServiceProperty()->getDetail($this->selectedMediaId);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getMediaServiceProperty()->getFilterOptions();
    }

    public function getCanUploadProductProperty(): bool
    {
        return Gate::allows('create', Product::class);
    }

    public function getBlogUploadUrlProperty(): string
    {
        return BlogPostResource::getUrl('create');
    }

    public function getProductUploadUrlProperty(): string
    {
        return ProductResource::getUrl('create');
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'type' => $this->typeFilter,
            'source' => $this->sourceFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['grid', 'list'], true) ? $mode : 'grid';
    }

    public function openDetailDrawer(string $id): void
    {
        $this->selectedMediaId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedMediaId = null;
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
        $this->typeFilter = [];
        $this->sourceFilter = [];
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

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilter(): void
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
            || filled($this->typeFilter)
            || filled($this->sourceFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function toggleSelect(string $id): void
    {
        $key = array_search($id, $this->selectedIds, true);

        if ($key === false) {
            $this->selectedIds[] = $id;
        } else {
            unset($this->selectedIds[$key]);
            $this->selectedIds = array_values($this->selectedIds);
        }
    }

    public function toggleSelectAll(): void
    {
        $pageIds = $this->getMediaItemsProperty()->pluck('id')->all();

        if (count(array_intersect($this->selectedIds, $pageIds)) === count($pageIds) && count($pageIds) > 0) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, $pageIds));
        } else {
            $this->selectedIds = array_values(array_unique(array_merge($this->selectedIds, $pageIds)));
        }
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.marketing.media.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'type' => $this->typeFilter ?: null,
            'source' => $this->sourceFilter ?: null,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}
