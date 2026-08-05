@php
$applications = $this->applications;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedApplication = $this->selectedApplication;
@endphp

<div class="vestra-workspace vestra-applications">
    <x-applications.page-header
        title="Applications"
        description="Review and manage incoming distributor applications."
        :csv-url="$this->getExportUrl('csv')"
        :excel-url="$this->getExportUrl('excel')"
        :pdf-url="$this->getExportUrl('pdf')"
    />

    <section class="vestra-workspace__section" aria-label="Application metrics">
        <x-applications.kpi-cards :cards="$kpiCards" />
    </section>

    <section class="vestra-workspace__section vestra-applications__content" aria-label="Application list">
        <div class="vestra-card vestra-applications__table-card">
            <x-applications.filter-bar
                :status-options="\App\Enums\DistributorStatus::cases()"
                :priority-options="$filterOptions['priorities'] ?? []"
                :country-options="$filterOptions['countries'] ?? []"
                :region-options="$filterOptions['regions'] ?? []"
            />

            @if (count($selectedIds) > 0)
                <div class="vestra-applications__bulk-bar" role="region" aria-label="Bulk actions">
                    <span class="vestra-applications__bulk-count">{{ count($selectedIds) }} selected</span>
                    <div class="vestra-applications__bulk-actions">
                        <button
                            type="button"
                            wire:click="bulkApprove"
                            wire:confirm="Approve the selected applications and create distributor accounts?"
                            class="vestra-button vestra-button--secondary"
                        >
                            Approve Selected
                        </button>
                        <button
                            type="button"
                            wire:click="bulkReject"
                            wire:confirm="Reject the selected applications?"
                            class="vestra-button vestra-button--secondary"
                        >
                            Reject Selected
                        </button>
                    </div>
                </div>
            @endif

            @if ($applications->total() > 0)
                <x-applications.application-table
                    :applications="$applications"
                    :sort-field="$sortField"
                    :sort-direction="$sortDirection"
                    :selected-ids="$selectedIds"
                />

                <x-applications.pagination :paginator="$applications" />
            @else
                <x-applications.empty-state
                    :has-filters="$this->hasActiveFilters()"
                />
            @endif
        </div>
    </section>

    <x-applications.detail-drawer
        :show="$showDetailDrawer"
        :application="$selectedApplication"
    />
</div>
