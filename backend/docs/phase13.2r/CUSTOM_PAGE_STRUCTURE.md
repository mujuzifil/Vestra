# Custom Page Structure — Phase 13.2R

## Dashboard Page Class
`app/Filament/Pages/Dashboard.php`

| Property | Value | Purpose |
|----------|-------|---------|
| `$routePath` | `/` | Makes the dashboard the admin home page. |
| `$layout` | `filament.layouts.crm` | Uses the custom CRM shell. |
| `$view` | `filament.pages.workspace-dashboard` | Custom dashboard view. |
| `$navigationGroup` | `Workspace` | Groups the link in the sidebar. |
| `$navigationSort` | `-2` | Places it first. |

## Layout File
`resources/views/filament/layouts/crm.blade.php`
- Receives `$livewire` from Filament.
- Renders `<x-admin.sidebar />`, `<x-admin.header />`, and `<x-admin.content-shell>`.
- Includes mobile sidebar overlay and toggle event handling.

## Page View
`resources/views/filament/pages/workspace-dashboard.blade.php`
- Calls `WorkspaceDataService` methods.
- Uses dashboard components (`kpi-card`, `chart-container`, `activity-item`, etc.).
- Loads `dashboard-chart.js` via Vite.

## Reusable Components

### Shell
- `components/admin/sidebar.blade.php`
- `components/admin/header.blade.php`
- `components/admin/content-shell.blade.php`

### Dashboard
- `components/admin/kpi-card.blade.php`
- `components/admin/chart-container.blade.php`
- `components/admin/activity-item.blade.php`
- `components/admin/notification-item.blade.php`
- `components/admin/empty-state.blade.php`
