@php
$activities = $this->activities;
$kpiCards = $this->kpiCards;
$selectedActivity = $this->selectedActivity;
$filterOptions = $this->filterOptions;
@endphp

<x-filament-panels::page>
    <div class="vestra-workspace vestra-activity">
        <x-activity.page-header
            title="Activity"
            description="Track and review all activities across the system in real time."
        />

        <section class="vestra-workspace__section" aria-label="Activity metrics">
            <x-activity.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-activity__content" aria-label="Activity feed">
            <div class="vestra-card vestra-activity__feed-card">
                <x-activity.filter-bar
                    :category-options="\App\Enums\ActivityCategory::cases()"
                    :status-options="\App\Enums\ActivityStatus::cases()"
                    :module-options="$filterOptions['modules'] ?? []"
                    :user-options="$filterOptions['users'] ?? []"
                    :selected-ids="$selectedIds"
                />

                @if ($activities->total() > 0)
                    <x-activity.activity-feed
                        :activities="$activities"
                        :selected-ids="$selectedIds"
                    />

                    <x-activity.pagination :paginator="$activities" />
                @else
                    <x-activity.empty-state
                        :has-filters="$this->hasActiveFilters()"
                    />
                @endif
            </div>
        </section>

        <x-activity.detail-drawer
            :show="$showDetailPanel"
            :activity="$selectedActivity"
        />
    </div>
</x-filament-panels::page>
