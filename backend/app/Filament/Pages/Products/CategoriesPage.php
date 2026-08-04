<?php

namespace App\Filament\Pages\Products;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Admin\CategoryAdminService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class CategoriesPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Products';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.products.categories';

    protected static ?string $slug = 'products/categories';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'sort_order';

    #[Url(as: 'direction')]
    public string $sortDirection = 'asc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedCategoryId = null;

    public function getTitle(): string
    {
        return 'Categories';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
    }

    public function getCategoryServiceProperty(): CategoryAdminService
    {
        return app(CategoryAdminService::class);
    }

    public function getCategoriesProperty(): mixed
    {
        return $this->getCategoryServiceProperty()
            ->paginateCategories($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getCategoryServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedCategoryProperty(): ?array
    {
        if (empty($this->selectedCategoryId)) {
            return null;
        }

        $category = Category::query()->find($this->selectedCategoryId);

        if ($category === null) {
            return null;
        }

        Gate::authorize('view', $category);

        return $this->getCategoryServiceProperty()->getDetail($category);
    }

    public function canCreateCategory(): bool
    {
        return Gate::allows('create', Category::class);
    }

    public function getCreateUrl(): string
    {
        return CategoryResource::getUrl('create');
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $category = Category::query()->findOrFail($id);
        Gate::authorize('view', $category);

        $this->selectedCategoryId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedCategoryId = null;
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
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'sort_order';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
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

    public function export(string $format)
    {
        Gate::authorize('export', Category::class);

        $allowed = ['csv', 'excel', 'pdf'];
        $format = strtolower($format);

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        return redirect()->to($this->getExportUrl($format));
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.products.categories.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}
