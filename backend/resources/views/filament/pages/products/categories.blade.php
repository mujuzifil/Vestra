@php
$categories = $this->categories;
$kpiCards = $this->kpiCards;
$selectedCategory = $this->selectedCategory;
@endphp

<x-filament-panels::page>
    <div class="vestra-workspace vestra-categories">
        <x-categories.page-header
            title="Categories"
            description="Organize product catalog categories."
            :can-create="$this->canCreateCategory()"
            :create-url="$this->getCreateUrl()"
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Category metrics">
            <x-categories.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-categories__content" aria-label="Category list">
            <div class="vestra-card vestra-categories__table-card">
                <x-categories.filter-bar />

                @if ($categories->total() > 0)
                    <x-categories.category-table
                        :categories="$categories"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />

                    <x-categories.pagination :paginator="$categories" />
                @else
                    <x-categories.empty-state
                        :has-filters="$this->hasActiveFilters()"
                        :can-create="$this->canCreateCategory()"
                        :create-url="$this->getCreateUrl()"
                    />
                @endif
            </div>
        </section>

        <x-categories.detail-drawer
            :show="$showDetailDrawer"
            :category="$selectedCategory"
        />
    </div>
</x-filament-panels::page>
