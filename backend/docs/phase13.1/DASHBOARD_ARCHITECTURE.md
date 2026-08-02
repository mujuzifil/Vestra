# Workspace Dashboard Architecture

## Overview

Phase 13.1 replaces the legacy Filament dashboard with a premium Workspace Dashboard aligned to the VESTRA v3.0 admin portal.

## Layout

The dashboard is rendered by `App\Filament\Pages\Dashboard` using a custom Blade view at `resources/views/filament/pages/dashboard.blade.php`.

### Sections

1. **Header** — title, personalized welcome, and date-range selector.
2. **KPI Cards** — five live stat cards.
3. **Sales Overview + Recent Activity** — two-column grid (2:1 on desktop).
4. **My Tasks + Notifications + Calendar** — three-column grid on desktop.

## Widgets

| Widget | Class | Purpose |
|--------|-------|---------|
| KPI Cards | `KpiCardsWidget` | Aggregated counts and trends |
| Sales Overview Chart | `SalesOverviewChartWidget` | Estimated quote value over time |
| Recent Activity | `RecentActivityWidget` | Latest audit-log events |
| My Tasks | `MyTasksWidget` | Empty-state placeholder for future tasks |
| Notifications | `NotificationsWidget` | Database notifications for current admin |
| Calendar | `UpcomingEventsWidget` | Empty-state placeholder for future events |

All widgets extend Filament v3 widget classes and are lazy-loaded.

## Event Flow

The header date-range selector dispatches `dashboard-range-changed`. `SalesOverviewChartWidget` listens via `#[On('dashboard-range-changed')]` and refreshes its dataset.

## Navigation

The sidebar now contains exactly ten groups declared in `AdminPanelProvider::navigationGroups()`:

Workspace, Sales, Distributors, Customer Success, Products, Operations, Marketing, Analytics, Communications, Administration.

Legacy `E-Commerce` resources (`OrderResource`, `ReviewResource`) are hidden from navigation.
