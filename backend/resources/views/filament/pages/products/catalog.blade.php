@php
$products = $this->products;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$formOptions = $this->formOptions;
$selectedProduct = $this->selectedProduct;
$existingImages = $this->editingProductImages;
@endphp

<div class="vestra-workspace vestra-products">
    <x-products.page-header
        title="Products"
        description="Manage catalog products, pricing, and stock levels."
        :csv-url="$this->getExportUrl('csv')"
        :excel-url="$this->getExportUrl('excel')"
        :pdf-url="$this->getExportUrl('pdf')"
        :can-create="$this->canCreate"
    />

    <section class="vestra-workspace__section" aria-label="Product metrics">
        <x-products.kpi-cards :cards="$kpiCards" />
    </section>

    <section class="vestra-workspace__section vestra-products__content" aria-label="Product list">
        <div class="vestra-card vestra-products__table-card">
            <x-products.filter-bar
                :status-options="$filterOptions['statuses'] ?? []"
                :category-options="$filterOptions['categories'] ?? []"
                :stock-options="$filterOptions['stock_options'] ?? []"
                :featured-options="$filterOptions['featured_options'] ?? []"
            />

            @if ($products->total() > 0)
                <x-products.product-table
                    :products="$products"
                    :sort-field="$sortField"
                    :sort-direction="$sortDirection"
                    :selected-ids="$selectedIds"
                />

                <x-products.pagination :paginator="$products" />
            @else
                <x-products.empty-state
                    :has-filters="$this->hasActiveFilters()"
                    :can-create="$this->canCreate"
                />
            @endif
        </div>
    </section>

    <x-products.detail-drawer
        :show="$showDetailDrawer"
        :product="$selectedProduct"
        :can-edit="$this->canUpdateSelected"
        :can-delete="$this->canDeleteSelected"
    />

    <x-products.product-form
        :show="$showFormModal"
        :editing-product-id="$editingProductId"
        :form-options="$formOptions"
        :form="$form"
        :existing-images="$existingImages"
        :pending-media-assets="$pendingMediaAssets"
    />

    @livewire(\App\Livewire\Admin\MediaAssetPicker::class)
</div>
