# Phase 13.7 — Data Mapping

All UI values map to live `quote_requests` / `quote_request_items` data. Nothing is fabricated.

| UI | Source |
|----|--------|
| Quote # | `reference_number` |
| Company | `company_name`; optional `user.companyProfile` for industry / link |
| Contact | `full_name`, `email`, `phone` |
| Products | `items.product_name`, `package_size`, `quantity` |
| Sales Rep | `assignedUser` (`assigned_to`) |
| Estimated Value | `estimated_value` |
| Priority | `priority` (`low` / `medium` / `high`) |
| Status | `QuoteRequestStatus` enum |
| Expiry / close | `expected_close_date` |
| Created / Updated | timestamps |
| District / City | `district`, `city` |
| Requirements | `requirements` |
| Internal notes | `admin_notes` |
| Attachments | JSON `attachments` paths on public disk |
| Support tickets | `SupportTicket` where `user_id` matches quote `user_id` |
| Activity | `AuditLog` for quote subject / related quote actions |
| Approval history | Empty state (no approval entity yet) |

## Status Values (locked)

`pending`, `contacted`, `quoted`, `approved`, `declined`, `closed`

## KPI Mapping

| Card | Query |
|------|-------|
| Total Quotes | `count(*)` |
| Pending | `status = pending` |
| Approved | `status = approved` |
| Declined | `status = declined` |
| Total Value (MTD) | `sum(estimated_value)` for current month |

Trends compare against prior-month baselines only when historical data exists; otherwise the UI shows **No comparison available**.
