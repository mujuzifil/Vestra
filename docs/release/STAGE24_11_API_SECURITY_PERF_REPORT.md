# Stage 24.11 — API, Security & Performance Report (v2.1.0)

## API

- Quote submit always links `company_profile_id` (D-001)
- Account quotes scoped by company ownership (D-002)
- Company update strips privileged fields (D-003)
- Route shadowing fixed for notification preferences and credit summary (D-029)
- Forecast reports SQLite-safe (D-030)
- Admin list resources return `{ data, meta, links }` (D-031)

## Security / RBAC

- Filament denial returns HTTP 403 without Livewire TypeError (D-014)
- Stub CRM pages blocked (D-004)
- Staff assignee dropdowns exclude customers (D-010)
- Support ticket create authorized (D-011)
- Review helpful uses `Review` policy (D-028)
- Distributor branch delete allows owners (D-027)
- KI-001 residual risk accepted on release gate item 11 (operator rotation still required)

## Performance

- Indexes: `company_profiles.status`, `support_tickets.status`, `quote_requests(status, created_at)`
- Quote list eager-load reduced (D-008)
- Support avg resolution uses DB aggregate (D-009)
- Sitemap fetch timeout prevents hung builds (D-023)
