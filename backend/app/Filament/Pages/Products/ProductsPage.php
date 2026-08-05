<?php

namespace App\Filament\Pages\Products;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Services\Admin\ProductAdminService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ProductsPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Products';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.products.catalog';

    protected static ?string $slug = 'products/catalog';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'category')]
    public array $categoryFilter = [];

    #[Url(as: 'stock')]
    public ?string $stockFilter = null;

    #[Url(as: 'featured')]
    public ?string $featuredFilter = null;

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    /** @var array<int, int> */
    public array $selectedIds = [];

    public bool $showDetailDrawer = false;

    public ?int $selectedProductId = null;

    public function getTitle(): string
    {
        return 'Products';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', Product::class);
    }

    public function getProductServiceProperty(): ProductAdminService
    {
        return app(ProductAdminService::class);
    }

    public function getProductsProperty(): mixed
    {
        return $this->getProductServiceProperty()
            ->paginateProducts($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getProductServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedProductProperty(): ?array
    {
        if (empty($this->selectedProductId)) {
            return null;
        }

        $product = Product::query()->find($this->selectedProductId);

        if ($product === null) {
            return null;
        }

        Gate::authorize('view', $product);

        return $this->getProductServiceProperty()->getDetail($product);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getProductServiceProperty()->getFilterOptions();
    }

    public function getCanCreateProperty(): bool
    {
        return Gate::allows('create', Product::class);
    }

    public function getCreateUrlProperty(): string
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
            'status' => $this->statusFilter,
            'category' => $this->categoryFilter,
            'stock' => $this->stockFilter,
            'featured' => $this->featuredFilter,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        Gate::authorize('view', $product);

        $this->selectedProductId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedProductId = null;
    }

    public function toggleSelectAll(): void
    {
        $pageIds = $this->getProductsProperty()->pluck('id')->map(fn ($id) => (int) $id)->all();

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
        $this->categoryFilter = [];
        $this->stockFilter = null;
        $this->featuredFilter = null;
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

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStockFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFeaturedFilter(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->categoryFilter)
            || filled($this->stockFilter)
            || ($this->featuredFilter !== null && $this->featuredFilter !== '');
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.products.catalog.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'category' => $this->categoryFilter ?: null,
            'stock' => $this->stockFilter,
            'featured' => $this->featuredFilter,
        ]);
    }
}
