@php
$staff = $this->staff;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedStaff = $this->selectedStaff;
@endphp

<x-filament-panels::page>
    <div class="vestra-workspace vestra-staff">
        <x-staff.page-header
            title="Staff"
            description="Manage administrators, roles, and account access."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
            :can-create="$this->canCreate"
            :create-url="$this->createUrl"
        />

        <section class="vestra-workspace__section" aria-label="Staff metrics">
            <x-staff.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-staff__content" aria-label="Staff list">
            <div class="vestra-card vestra-staff__table-card">
                <x-staff.filter-bar
                    :status-options="$filterOptions['statuses'] ?? []"
                    :role-options="$filterOptions['roles'] ?? []"
                />

                @if ($staff->total() > 0)
                    <x-staff.staff-table
                        :staff="$staff"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />

                    <x-staff.pagination :paginator="$staff" />
                @else
                    <x-staff.empty-state
                        :has-filters="$this->hasActiveFilters()"
                        :can-create="$this->canCreate"
                        :create-url="$this->createUrl"
                    />
                @endif
            </div>
        </section>

        <x-staff.detail-drawer
            :show="$showDetailDrawer"
            :staff="$selectedStaff"
        />
    </div>
</x-filament-panels::page>
