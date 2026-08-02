# Phase 13.2 — Component Library

## Existing Shared Components

| Component | Path | Purpose |
|-----------|------|---------|
| sidebar | `components/admin/sidebar.blade.php` | Main navigation sidebar |
| header | `components/admin/header.blade.php` | Top application bar |
| content-shell | `components/admin/content-shell.blade.php` | Content wrapper |
| kpi-card | `components/admin/kpi-card.blade.php` | Dashboard metric card |
| chart-container | `components/admin/chart-container.blade.php` | Chart wrapper |
| activity-item | `components/admin/activity-item.blade.php` | Activity feed row |
| notification-item | `components/admin/notification-item.blade.php` | Notification row |
| empty-state | `components/admin/empty-state.blade.php` | Empty state illustration |

## KPI Card Update

The KPI card was adjusted to match the reference image:
- Icon on the left
- Label above value on the right
- Trend pill below with icon, trend value, and comparison label

CSS classes:
- `.vestra-kpi-card`
- `.vestra-kpi-card__main`
- `.vestra-kpi-card__icon`
- `.vestra-kpi-card__content`
- `.vestra-kpi-card__label`
- `.vestra-kpi-card__value`
- `.vestra-kpi-card__trend`
- `.vestra-kpi-card__trend--up`
- `.vestra-kpi-card__trend--down`
- `.vestra-kpi-card__trend--neutral`

## Sidebar Footer

Added a dedicated Collapse action in the sidebar footer to match the reference UI.
