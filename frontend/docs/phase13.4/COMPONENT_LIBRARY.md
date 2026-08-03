# Component Library — Phase 13.4

## Blade Components

### `components/notifications/page-header.blade.php`

Props: `title`, `description`, `hasUnread`

Renders the Workspace hero with refresh and Mark All Read actions.

### `components/notifications/kpi-cards.blade.php`

Props: `cards`

Wraps `x-admin.kpi-card` for notification metrics.

### `components/notifications/filter-bar.blade.php`

Props: `priorityOptions`, `categoryOptions`, `typeOptions`, `selectedIds`

Search input, dropdown filters, date range, reset button, and bulk action bar.

### `components/notifications/notification-feed.blade.php`

Props: `notifications`, `selectedIds`, `sortField`, `sortDirection`

Column header and list of notification cards.

### `components/notifications/notification-card.blade.php`

Props: `notification`, `selected`

Individual notification row with icon, content, badges, timestamp, and quick actions.

### `components/notifications/detail-panel.blade.php`

Props: `show`, `notification`

Slide-out panel with full notification details.

### `components/notifications/empty-state.blade.php`

Props: `hasFilters`

Contextual empty state for no notifications or no filter matches.

### `components/notifications/pagination.blade.php`

Props: `paginator`

Custom pagination controls matching the Tasks Workspace.

### `components/notifications/badge.blade.php`

Props: `value`, `color`

Small rounded badge used for category and priority labels.

## CSS

`resources/css/filament/admin/components/notifications.css` contains Workspace-specific styles while importing shared variables from the CRM theme.
