@php
$stocks = $this->stocks;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedStock = $this->selectedStock;
@endphp
    <div class="vestra-workspace vestra-inventory">
        <x-inventory.page-header
            title="Inventory"
            description="Monitor warehouse stock levels, availability, and adjustments."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Inventory metrics">
            <x-inventory.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-inventory__content" aria-label="Stock list">
            <div class="vestra-card vestra-inventory__table-card">
                <x-inventory.filter-bar
                    :warehouse-options="$filterOptions['warehouses'] ?? []"
                    :category-options="$filterOptions['categories'] ?? []"
                    :stock-status-options="$filterOptions['stock_statuses'] ?? []"
                />

                @if ($stocks->total() > 0)
                    <x-inventory.stock-table
                        :stocks="$stocks"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />

                    <x-inventory.pagination :paginator="$stocks" />
                @else
                    <x-inventory.empty-state :has-filters="$this->hasActiveFilters()" />
                @endif
            </div>
        </section>

        <x-inventory.detail-drawer
            :show="$showDetailDrawer"
            :stock="$selectedStock"
        />
    </div>
