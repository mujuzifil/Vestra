# Phase 13.11 — Data Mapping

All values shown in the Active Partners workspace are sourced live from the database. No mock, seeded-only, or hardcoded display values are used.

## KPI Cards

| Card | Source | Notes |
|------|--------|-------|
| Total Partners | `COUNT(distributors)` | Trend compares to count as of start of previous month |
| Active Partners | `COUNT(distributors WHERE status = 'active')` | Same trend method |
| Suspended Partners | `COUNT(distributors WHERE status = 'suspended')` | Same trend method |
| Revenue (This Month) | `SUM(orders.total_amount) WHERE orders.distributor_id IS NOT NULL AND payment_status = 'paid'` for current calendar month | **Conditionally rendered** — only shown if the `orders` table has both `distributor_id` and `payment_status` columns (verified via `Schema::hasColumn`). Trend compares to the same aggregate for the previous month. |
| Credit Outstanding | `SUM(credit_accounts.balance)` across all distributors | No historical snapshot exists yet, so trend is intentionally marked "No comparison available" rather than fabricated |

No "Top Performing Territory" or similar unranked/derived KPI is shown, since there is no live aggregation source for territory performance in this phase (Territories/performance analytics land in Phase 13.12+).

## Table Columns

| Column | Source |
|--------|--------|
| Partner | `distributors.company_name` + generated code `PART-{id}` |
| Territory | `distributors.district` (fallback `city`) |
| Country | `distributors.country` |
| Partner Type | `distributors.business_type` |
| Account Manager | `salesRepresentative.name` (relation: `distributors.sales_representative_id` → `sales_representatives.id`) |
| Credit Limit | `creditAccount.limit` |
| Credit Utilization | `creditAccount->utilizationPercentage()` = `(balance + authorized_amount) / limit * 100`, capped at 100 |
| Outstanding | `creditAccount.balance` |
| Status | `distributors.status` (`DistributorAccountStatus` enum) |

## Detail Drawer

| Section | Source |
|---------|--------|
| Company | `distributors.*` (registration, tax ID, industry, years in business, website, address) |
| Primary Contact | `distributors.primary_contact_name`, `email`, `phone` |
| Account Manager | `salesRepresentative` relation |
| Credit | `creditAccount` relation (limit, balance, authorized amount, available credit, utilization %) |
| Branches | `branches` relation (`distributor_branches`) |
| Documents | `documents` relation (`distributor_documents`) |
| Recent Orders | `Order::where('distributor_id', ...)->latest()->limit(5)` |
| Related Activity | `AuditLog` scoped to `subject_type = Distributor::class` |

## Filters

| Filter | Source |
|--------|--------|
| Search | `company_name`, `trading_name`, `email`, `phone`, `registration_number`, `primary_contact_name` (case-insensitive `LIKE`) |
| Status | `DistributorAccountStatus::cases()` |
| Country | Distinct `distributors.country` |
| Region | Distinct `distributor_service_areas.region` (joined via `whereHas('serviceAreas', ...)`) |
| Sales Rep | `sales_representatives` table |
