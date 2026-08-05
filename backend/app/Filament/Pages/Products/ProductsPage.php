<?php

namespace App\Filament\Pages\Products;

use App\Enums\ProductStatus;
use App\Enums\ProductStockStatus;
use App\Models\Product;
use App\Services\Admin\ProductAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProductsPage extends Page
{
    use WithFileUploads;
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

    public bool $showFormModal = false;

    public ?int $editingProductId = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $imageUploads = [];

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
        $this->resetForm();
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

    /**
     * @return array<string, mixed>
     */
    public function getFormOptionsProperty(): array
    {
        return $this->getProductServiceProperty()->getFormOptions();
    }

    public function getCanCreateProperty(): bool
    {
        return Gate::allows('create', Product::class);
    }

    public function getEditingProductImagesProperty(): array
    {
        if (empty($this->editingProductId)) {
            return [];
        }

        $product = Product::query()->find($this->editingProductId);

        if ($product === null) {
            return [];
        }

        return $this->getProductServiceProperty()->getDetail($product)['images'] ?? [];
    }

    public function getCanUpdateSelectedProperty(): bool
    {
        if (empty($this->selectedProductId) && empty($this->editingProductId)) {
            return false;
        }

        $id = $this->editingProductId ?? $this->selectedProductId;
        $product = Product::query()->find($id);

        return $product !== null && Gate::allows('update', $product);
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

    public function openCreateModal(): void
    {
        Gate::authorize('create', Product::class);

        $this->resetForm();
        $this->editingProductId = null;
        $this->showFormModal = true;
    }

    public function openEditModal(?int $id = null): void
    {
        $id ??= $this->selectedProductId;
        $product = Product::query()->findOrFail($id);
        Gate::authorize('update', $product);

        $options = $this->getFormOptionsProperty();

        $this->editingProductId = $product->id;
        $this->form = [
            'name' => $product->name ?? '',
            'sku' => $product->sku ?? '',
            'short_description' => $product->short_description ?? '',
            'description' => $product->description ?? '',
            'category_id' => $product->category_id,
            'price' => $product->price !== null ? (string) $product->price : '',
            'cost_price' => $product->cost_price !== null ? (string) $product->cost_price : '',
            'currency' => $product->currency ?? ($options['default_currency'] ?? ''),
            'cost_currency' => $product->cost_currency ?? ($options['default_currency'] ?? ''),
            'stock_quantity' => (string) ($product->stock_quantity ?? 0),
            'low_stock_threshold' => $product->low_stock_threshold !== null
                ? (string) $product->low_stock_threshold
                : (string) ($options['default_low_stock_threshold'] ?? 10),
            'stock_status' => $product->resolvedStockStatus()->value,
            'unit' => $product->unit ?? '',
            'weight' => $product->weight !== null ? (string) $product->weight : '',
            'barcode' => $product->barcode ?? '',
            'featured' => (bool) $product->featured,
            'status' => $product->status instanceof ProductStatus ? $product->status->value : (string) $product->status,
            'tax_rate' => $product->tax_rate !== null ? (string) $product->tax_rate : '',
        ];
        $this->imageUploads = [];
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->editingProductId = null;
        $this->imageUploads = [];
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function saveProduct(): void
    {
        $validated = $this->validate($this->formRules());

        $service = $this->getProductServiceProperty();
        $uploads = array_values(array_filter(
            $this->imageUploads,
            fn ($file) => $file instanceof TemporaryUploadedFile
        ));

        $wasEditing = $this->editingProductId !== null;
        $keepDetailsOpen = $this->showDetailDrawer;

        if ($wasEditing) {
            $product = Product::query()->findOrFail($this->editingProductId);
            Gate::authorize('update', $product);
            $product = $service->updateProduct($product, $validated['form'], $uploads, auth()->user());

            Notification::make()->title('Product updated')->success()->send();
        } else {
            Gate::authorize('create', Product::class);
            $product = $service->createProduct($validated['form'], $uploads, auth()->user());

            Notification::make()->title('Product created')->success()->send();
        }

        $this->closeFormModal();
        $this->selectedProductId = $product->id;

        if ($keepDetailsOpen || ! $wasEditing) {
            $this->showDetailDrawer = true;
        }
    }

    public function removeProductImage(int $imageId): void
    {
        if (! $this->editingProductId) {
            return;
        }

        $product = Product::query()->findOrFail($this->editingProductId);
        Gate::authorize('update', $product);

        $this->getProductServiceProperty()->removeImage($product, $imageId);

        Notification::make()->title('Image removed')->success()->send();
    }

    public function removeUpload(int $index): void
    {
        unset($this->imageUploads[$index]);
        $this->imageUploads = array_values($this->imageUploads);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formRules(): array
    {
        $productId = $this->editingProductId;

        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'form.short_description' => ['nullable', 'string', 'max:1000'],
            'form.description' => ['nullable', 'string'],
            'form.category_id' => ['required', 'integer', 'exists:categories,id'],
            'form.price' => ['required', 'numeric', 'min:0'],
            'form.cost_price' => ['nullable', 'numeric', 'min:0'],
            'form.currency' => ['nullable', 'string', 'max:3'],
            'form.cost_currency' => ['nullable', 'string', 'max:3'],
            'form.stock_quantity' => ['required', 'integer', 'min:0'],
            'form.low_stock_threshold' => ['required', 'integer', 'min:0'],
            'form.stock_status' => ['required', Rule::in(array_column(ProductStockStatus::cases(), 'value'))],
            'form.unit' => ['nullable', 'string', 'max:100'],
            'form.weight' => ['nullable', 'numeric', 'min:0'],
            'form.barcode' => ['nullable', 'string', 'max:255'],
            'form.featured' => ['sometimes', 'boolean'],
            'form.status' => ['required', Rule::in(array_column(ProductStatus::cases(), 'value'))],
            'form.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'imageUploads' => ['nullable', 'array'],
            'imageUploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function resetForm(): void
    {
        $options = app(ProductAdminService::class)->getFormOptions();
        $defaultTax = $options['default_tax_rate'] ?? null;

        $this->form = [
            'name' => '',
            'sku' => '',
            'short_description' => '',
            'description' => '',
            'category_id' => null,
            'price' => '',
            'cost_price' => '',
            'currency' => $options['default_currency'] ?? '',
            'cost_currency' => $options['default_currency'] ?? '',
            'stock_quantity' => '0',
            'low_stock_threshold' => (string) ($options['default_low_stock_threshold'] ?? 10),
            'stock_status' => ProductStockStatus::IN_STOCK->value,
            'unit' => '',
            'weight' => '',
            'barcode' => '',
            'featured' => false,
            'status' => ProductStatus::ACTIVE->value,
            'tax_rate' => ($defaultTax !== null && $defaultTax !== '') ? (string) $defaultTax : '',
        ];
        $this->imageUploads = [];
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
