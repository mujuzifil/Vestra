# Component Library — Phase 13.2R

## Shell Components

### `components/admin/sidebar.blade.php`
Props: none (uses Filament navigation and auth).
Renders the CRM sidebar.

### `components/admin/header.blade.php`
Props: `dateRange`.
Renders the CRM header.

### `components/admin/content-shell.blade.php`
Props: slot.
Wraps page content with consistent container.

## Dashboard Components

### `components/admin/kpi-card.blade.php`
Props: `icon`, `label`, `value`, `trend`, `trendLabel`, `trendPositive`, `color`.

### `components/admin/chart-container.blade.php`
Props: `id`, `title`, `labels`, `values`, `empty`.
Wraps a Chart.js canvas and passes JSON data to `dashboard-chart.js`.

### `components/admin/activity-item.blade.php`
Props: `icon`, `color`, `title`, `subtitle`, `time`, `url`.

### `components/admin/notification-item.blade.php`
Props: `icon`, `title`, `body`, `time`, `read`.

### `components/admin/empty-state.blade.php`
Props: `icon`, `title`, `description`, `actionText`, `actionHref`.

## CSS
`components/crm-shell.css` defines all CRM component classes.
