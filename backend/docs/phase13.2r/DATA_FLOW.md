# Data Flow — Phase 13.2R

## KPI Cards
1. `Dashboard` page calls `WorkspaceDataService::getKpiCards()`.
2. Service queries cached counts for open quotes, pending distributor applications, open tickets, MTD revenue, and active products.
3. Each card calculates a trend versus the previous period.
4. Blade `kpi-card` component renders icon, label, value, and trend badge.

## Sales Overview Chart
1. Page calls `WorkspaceDataService::getSalesOverviewData($dateRange)`.
2. Service resolves period bounds and sums `estimated_value` per day from `QuoteRequest`.
3. Result is cached and passed to `chart-container` as JSON.
4. `dashboard-chart.js` reads the payload and renders a Chart.js line chart.

## Recent Activity
1. Service queries `AuditLog` records, eager-loads `user`, filters login events.
2. Maps each log to an icon, color, title, subtitle, URL, and relative time.
3. `activity-item` component renders each row.

## Notifications
1. Service fetches the authenticated user's notifications.
2. Returns unread count and mapped items.
3. `notification-item` component renders each row with unread highlight.

## Tasks & Calendar
- No backend data yet.
- Rendered via shared `empty-state` component.
