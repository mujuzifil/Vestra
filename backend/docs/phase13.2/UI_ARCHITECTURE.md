# UI Architecture — Phase 13.2

## Goal
Transform the VESTRA Admin Portal Workspace Dashboard into a premium enterprise CRM interface while preserving all business logic, data sources, and policies.

## Layers

### 1. Global Shell (Filament Panel)
- `AdminPanelProvider` configures the panel: domain, brand logo, font, colours, navigation groups.
- `theme.css` loads design tokens and component imports.
- `navigation.css` overrides the default Filament sidebar and topbar.

### 2. Design Tokens
Located in `backend/resources/css/filament/admin/tokens/`:
- `colors.css` — VESTRA navy, green, gold, semantic colours.
- `spacing.css` — 4px grid, sidebar/topbar dimensions, max content width.
- `typography.css` — Poppins scale.
- `radius.css`, `elevation.css`, `motion.css` — shared visual primitives.

### 3. Dashboard Page
- `Dashboard.php` registers widgets and exposes the `dateRange` URL parameter.
- `filament/pages/dashboard.blade.php` renders the custom page header, date selector, and widget grid.
- `dashboard.css` provides the dashboard-specific layout, KPI card grid, and widget styling.

### 4. Widgets
- `KpiCardsWidget` — `StatsOverviewWidget` subclass using live cached counts and trends.
- `SalesOverviewChartWidget` — `ChartWidget` subclass rendering a Chart.js line chart.
- `RecentActivityWidget`, `NotificationsWidget`, `MyTasksWidget`, `UpcomingEventsWidget` — custom Blade widgets.

### 5. Shared Components
- `components/admin/empty-state.blade.php` — reusable empty state with icon, title, description, optional action.

## Key Decisions
- CSS-only visual overrides avoid forking Filament components.
- Widget data logic is untouched; only presentation layers changed.
- All values remain live; no placeholder metrics introduced.
