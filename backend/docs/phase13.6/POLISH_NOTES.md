# Phase 13.6r — Companies Polish Notes

## Purpose

Visual/UX polish of the shipped Companies workspace against `Companies.png`, without rebuilding Phase 13.6.

## Added

- Right-side filter panel (status, industry, country, region, district, account manager, dates, relationship toggles)
- Compact inline filters (status, industry, country, account manager) + Filters badge count
- `hasDistributor` filter via `CompanyProfile::scopeWithDistributor`
- Bulk row selection
- Table columns: Contact, Phone, Region, District, Last Activity (`updated_at`)
- Eager `withCount` for open quotes and active tickets (N+1 removal)

## Intentionally skipped (data integrity)

| Mockup element | Reason |
|----------------|--------|
| Revenue (MTD) KPI/column | No per-company revenue source |
| Opportunities KPI / Open Opportunities column | No Opportunity model |
| Contacts count column | No multi-contact relation; only primary contact fields |

Existing integrity-safe KPIs remain: Total, Active, New This Month, With Open Quotes, With Active Tickets.
