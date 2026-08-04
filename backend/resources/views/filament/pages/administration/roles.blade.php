@php
$roles = $this->roles;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedRole = $this->selectedRole;
@endphp

<x-filament-panels::page>
    <div class="vestra-workspace vestra-roles">
        <x-roles.page-header
            title="Roles"
            description="Manage system roles and control access permissions across the platform."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
            :can-create="$this->canCreate"
            :create-url="$this->createUrl"
        />

        <section class="vestra-workspace__section" aria-label="Roles metrics">
            <x-roles.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section" aria-label="Roles list">
            <div class="vestra-card vestra-roles__table-card">
                <x-roles.filter-bar :type-options="$filterOptions['types'] ?? []" />

                @if ($roles->total() > 0)
                    <x-roles.role-table :roles="$roles" :sort-field="$sortField" :sort-direction="$sortDirection" />
                    <x-roles.pagination :paginator="$roles" />
                @else
                    <x-roles.empty-state
                        :has-filters="$this->hasActiveFilters()"
                        :can-create="$this->canCreate"
                        :create-url="$this->createUrl"
                    />
                @endif
            </div>
        </section>

        <x-roles.detail-drawer :show="$showDetailDrawer" :role="$selectedRole" />
    </div>
</x-filament-panels::page>
