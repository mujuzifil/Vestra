# Architecture — Phase 13.2R

## Goal
Replace the Filament Dashboard widget architecture with a fully custom CRM Workspace Dashboard that uses Filament only for authentication, authorization, resources, and backend services.

## Principles
- Filament provides the panel, auth, policies, and resources.
- The UI shell, layout, and dashboard composition are custom Blade + Tailwind.
- All data remains live; no placeholder statistics.
- The shell must be reusable for future admin pages.

## Layers

### 1. Custom CRM Layout
`resources/views/filament/layouts/crm.blade.php`
- Wraps the page in the base Filament HTML/head/body.
- Renders custom sidebar, header, and content shell.
- Keeps Filament notifications and scripts.

### 2. Application Shell Components
- `components/admin/sidebar.blade.php` — VESTRA-branded sidebar with grouped navigation.
- `components/admin/header.blade.php` — global search, notifications, user menu, date selector.
- `components/admin/content-shell.blade.php` — consistent content container.

### 3. Dashboard Page
`app/Filament/Pages/Dashboard.php`
- Extends `Filament\Pages\Page`.
- Overrides `getRoutePath()` to `/`.
- Uses custom layout and view.
- Listens for `dashboard-range-changed` events.

### 4. Data Service
`app/Services/Admin/WorkspaceDataService.php`
- Aggregates KPI cards, chart data, recent activity, and notifications.
- Caches results and eager-loads relationships.

### 5. Dashboard Sections
`resources/views/filament/pages/workspace-dashboard.blade.php`
- Hero section.
- KPI grid.
- Sales Overview chart.
- Recent Activity.
- Tasks / Notifications / Calendar columns.

### 6. Chart Module
`resources/js/admin/dashboard-chart.js`
- Renders Chart.js line chart from JSON data payload.
- Built as a separate Vite entry.

## Routing
The Dashboard page is registered in `AdminPanelProvider::pages()` and exposes the root `/` route of the admin domain.
