@php
$notifications = $this->notifications;
$kpiCards = $this->kpiCards;
$selectedNotification = $this->selectedNotification;
@endphp

<div class="vestra-workspace vestra-notifications">
    <x-notifications.page-header
        title="Notifications"
        description="Your workspace notifications, system alerts, and activity updates."
        :has-unread="(int) ($kpiCards[1]['value'] ?? 0) > 0"
    />

    <section class="vestra-workspace__section" aria-label="Notification metrics">
        <x-notifications.kpi-cards :cards="$kpiCards" />
    </section>

    <section class="vestra-workspace__section vestra-notifications__content" aria-label="Notification feed">
        <div class="vestra-card vestra-notifications__feed-card">
            <x-notifications.filter-bar
                :priority-options="\App\Enums\NotificationPriority::cases()"
                :category-options="\App\Enums\NotificationCategory::cases()"
                :type-options="\App\Enums\NotificationType::cases()"
                :selected-ids="$selectedIds"
            />

            @if ($notifications->total() > 0)
                <x-notifications.notification-feed
                    :notifications="$notifications"
                    :selected-ids="$selectedIds"
                    :sort-field="$sortField"
                    :sort-direction="$sortDirection"
                />

                <x-notifications.pagination :paginator="$notifications" />
            @else
                <x-notifications.empty-state
                    :has-filters="$this->hasActiveFilters()"
                />
            @endif
        </div>
    </section>

    <x-notifications.detail-panel
        :show="$showDetailPanel"
        :notification="$selectedNotification"
    />
</div>
