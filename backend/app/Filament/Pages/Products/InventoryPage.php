<?php

namespace App\Filament\Pages\Products;

use App\Models\ProductWarehouseStock;
use App\Services\Admin\InventoryAdminService;
use App\Services\AuditService;
use App\Services\InventoryService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class InventoryPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static bool $isDiscovered = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Products';

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.products.inventory';

    protected static ?string $slug = 'products/inventory';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'warehouse')]
    public array $warehouseFilter = [];

    #[Url(as: 'category')]
    public array $categoryFilter = [];

    #[Url(as: 'stock_status')]
    public array $stockStatusFilter = [];

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'updated_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedStockId = null;

    public string $adjustQuantity = '';

    public string $adjustReason = '';

    public function getTitle(): string
    {
        return 'Inventory';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(Gate::allows('viewAny', ProductWarehouseStock::class), 403);
    }

    public function getInventoryServiceProperty(): InventoryAdminService
    {
        return app(InventoryAdminService::class);
    }

    public function getStocksProperty(): mixed
    {
        return $this->getInventoryServiceProperty()
            ->paginateStock($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getInventoryServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedStockProperty(): ?array
    {
        if (empty($this->selectedStockId)) {
            return null;
        }

        $stock = ProductWarehouseStock::query()->find($this->selectedStockId);

        if ($stock === null) {
            return null;
        }

        Gate::authorize('view', $stock);

        return $this->getInventoryServiceProperty()->getDetail($stock);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getInventoryServiceProperty()->getFilterOptions();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'warehouse' => $this->warehouseFilter,
            'category' => $this->categoryFilter,
            'stock_status' => $this->stockStatusFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $stock = ProductWarehouseStock::query()->findOrFail($id);
        Gate::authorize('view', $stock);

        $this->selectedStockId = $id;
        $this->showDetailDrawer = true;
        $this->adjustQuantity = '';
        $this->adjustReason = '';
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedStockId = null;
        $this->adjustQuantity = '';
        $this->adjustReason = '';
    }

    public function adjustStock(): void
    {
        $stock = ProductWarehouseStock::query()->findOrFail($this->selectedStockId);
        Gate::authorize('update', $stock);

        $quantity = (int) $this->adjustQuantity;
        $reason = trim($this->adjustReason);

        if ($quantity === 0 || $reason === '') {
            Notification::make()
                ->title('Adjustment requires a non-zero quantity and a reason')
                ->warning()
                ->send();

            return;
        }

        $movement = app(InventoryService::class)->adjustStock(
            $stock->product,
            $stock->warehouse,
            $quantity,
            $reason,
            auth()->user()
        );

        AuditService::log(
            auth()->user(),
            'stock.adjusted',
            $stock,
            [
                'product_id' => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'adjustment' => $quantity,
                'balance_after' => $movement->balance_after,
                'reason' => $reason,
            ]
        );

        $this->adjustQuantity = '';
        $this->adjustReason = '';

        Notification::make()
            ->title('Stock adjusted')
            ->body('New balance: '.$movement->balance_after)
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
        $this->warehouseFilter = [];
        $this->categoryFilter = [];
        $this->stockStatusFilter = [];
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'updated_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->warehouseFilter)
            || filled($this->categoryFilter)
            || filled($this->stockStatusFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedWarehouseFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStockStatusFilter(): void
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

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.products.inventory.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'warehouse' => $this->warehouseFilter ?: null,
            'category' => $this->categoryFilter ?: null,
            'stock_status' => $this->stockStatusFilter ?: null,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}
