@php
$branches = $this->viewMode === 'table' ? $this->branches : null;
$mappableBranches = $this->viewMode === 'map' ? $this->mappableBranches : collect();
$unmappedCount = $this->viewMode === 'map' ? $this->unmappedCount : 0;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedBranch = $this->selectedBranch;
$activeFilterCount = $this->activeFilterCount();
$canCreate = $this->canCreateBranch();
@endphp
    <div class="vestra-workspace vestra-territories">
        <x-territories.page-header
            title="Territories"
            description="Visualize and manage distributor branch coverage across territories."
            :view-mode="$this->viewMode"
            :can-create="$canCreate"
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Territory metrics">
            <x-territories.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-territories__content" aria-label="Territory list">
            <div class="vestra-territories__layout @if ($showFilterPanel) vestra-territories__layout--with-panel @endif">
                <div class="vestra-territories__main">
                    <div class="vestra-card vestra-territories__table-card">
                        <x-territories.filter-bar
                            :country-options="$filterOptions['countries'] ?? []"
                            :district-options="$filterOptions['districts'] ?? []"
                            :distributor-options="$filterOptions['distributors'] ?? []"
                            :active-filter-count="$activeFilterCount"
                            :show-filter-panel="$showFilterPanel"
                            :country-filter="$countryFilter"
                            :district-filter="$districtFilter"
                            :distributor-filter="$distributorFilter"
                        />

                        @if ($this->viewMode === 'table')
                            @if ($branches->total() > 0)
                                <x-territories.branch-table
                                    :branches="$branches"
                                    :sort-field="$sortField"
                                    :sort-direction="$sortDirection"
                                />

                                <x-territories.pagination :paginator="$branches" />
                            @else
                                <x-territories.empty-state
                                    :has-filters="$this->hasActiveFilters()"
                                    :can-create="$canCreate"
                                />
                            @endif
                        @else
                            <x-territories.map-panel
                                :branches="$mappableBranches"
                                :unmapped-count="$unmappedCount"
                            />
                        @endif
                    </div>
                </div>

                @if ($showFilterPanel)
                    <x-territories.filter-panel
                        :show="true"
                        :status-options="$filterOptions['statuses'] ?? []"
                        :country-options="$filterOptions['countries'] ?? []"
                        :district-options="$filterOptions['districts'] ?? []"
                        :distributor-options="$filterOptions['distributors'] ?? []"
                        :country-filter="$countryFilter"
                        :district-filter="$districtFilter"
                        :status-filter="$statusFilter"
                        :distributor-filter="$distributorFilter"
                        :active-filter-count="$activeFilterCount"
                    />
                @endif
            </div>
        </section>

        <x-territories.detail-drawer
            :show="$showDetailDrawer"
            :branch="$selectedBranch"
        />
    </div>
