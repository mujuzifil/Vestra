# Performance Audit

## 1. Overview

The Admin Portal handles business-critical data. Performance issues directly affect sales and support response times. This audit identifies observable risks and provides remediation priorities based on the actual resource/widget inventory.

## 2. Observed Patterns

### 2.1 Eager Loading

Some resources use eager loading or counts:

- `CustomerResource` uses `withCount`, `withSum`, and `withMax` for quote/order aggregates.
- `QuoteRequestResource` loads related `customer`, `products`, `assignedRepresentative`, and `media`.
- `ProductResource` loads `category`, `media`, and warehouse stock relationships.
- `DistributorResource` loads credit account, branches, contacts, documents, quotations, orders, invoices.

These patterns are positive but not uniformly applied.

### 2.2 N+1 Risk Areas

| Area | Risk | Evidence |
|------|------|----------|
| Customer list | Aggregate columns (`lifetime_orders`, `lifetime_spend`) may trigger extra queries if not eager loaded or indexed. | Resource table columns |
| Quote index | Status badges and assigned representative names loaded per row. | View inspection |
| Distributor index | Status and region columns may load relations per row. | Resource table |
| Product media | Spatie media conversions fetched per image if not eager loaded. | ProductResource media columns |
| Blog post list | Author name and category loaded per row. | BlogPostResource columns |
| Notifications resource | Unread counts and sender avatar per notification. | NotificationsResource list |
| Dashboard widgets | `ExecutiveKpiWidget`, `OperationalKpiWidget`, and 15 other widgets aggregate across models independently, causing repeated full-table scans. | Widget code |
| Report pages | Each `ReportPage` runs separate queries for stats and charts; no shared cache. | Report page classes |

### 2.3 Heavy Dashboard Widgets

The dashboard hosts 17 widgets, including:

- `ExecutiveKpiWidget` — revenue/order aggregates.
- `OperationalKpiWidget` — pending orders, low stock, messages, reviews.
- `RevenueChartWidget` — 30-day paid revenue.
- `ForecastWidget` — revenue forecast.
- `CustomerIntelligenceWidget`, `DistributorIntelligenceWidget`, `InventoryIntelligenceWidget` — analytics from `ReportService`.
- `ApiHealthWidget`, `SearchAnalyticsWidget` — API/search analytics.
- `RecentOrdersWidget`, `TopDistributorsWidget`, `LowStockWidget`, `AlertsWidget`, `RecentActivityWidget`.

No caching layer was observed for dashboard aggregates; every admin landing triggers fresh counts.

### 2.4 Heavy Report Pages

14 report pages extend a `ReportPage` base. Many run unbounded date-range queries and lack caching. Report navigation also has sort collisions (Forecasting, Credit, DistributorReport all sort `60`).

### 2.5 Large Tables

- `QuoteRequestResource` and `DistributorRequestResource` tables include many columns and filters.
- Default pagination is applied but search scopes may scan text columns (`notes`, `requirements`) without full-text indexes.

### 2.6 Expensive Forms

- Quote and Distributor forms contain repeaters, file uploads, and relationship selects that load large datasets (e.g., all products, all customers).
- No deferred loading or searchable async selects observed universally.

## 3. Database Index Observations

- Status columns are frequently filtered but may lack composite indexes with `created_at`.
- Quote/customer foreign keys are indexed by Laravel conventions.
- Full-text search on enquiry messages and blog content is not configured.

## 4. Memory & CPU

- Report exports can load thousands of rows into memory if streaming is not enabled.
- Media conversions may run synchronously on upload, blocking form saves.

## 5. Placeholder Performance Impact

Several actions are placeholders (`Export CSV`, `Print Invoices`, `Convert to Order`). While they do not currently execute, dead links contribute to perceived slowness when users click them and nothing happens.

## 6. Recommendations

1. **Add composite indexes** on `(status, created_at)` for `quote_requests`, `distributor_requests`, `contact_messages`, and `orders`.
2. **Cache dashboard aggregates** for 5–15 minutes using Laravel cache; refresh in background if near-real-time is required.
3. **Eager-load consistently** across all table columns that traverse relationships (representative, customer, category, author).
4. **Use deferred/async selects** for customer, product, user, and distributor relationship fields.
5. **Enable full-text indexes** for searchable text fields or delegate to a search engine.
6. **Stream exports** and limit exportable row counts to prevent memory exhaustion.
7. **Queue media conversions** and large file processing.
8. **Add query logging/monitoring** in production to catch N+1 regressions.
9. **Introduce a `VestraTable` concern** that enforces default eager loads and pagination per resource.
10. **Resolve report page sort collisions** and add date-range caching.
