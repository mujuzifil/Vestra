# Phase 11 — Database Audit (Backend)

## Schema Changes Applied

- Added migration `2026_08_01_090000_add_requirements_to_quote_requests_table.php` to create the missing `requirements` text column.
- Added `requirements` to `QuoteRequest::$fillable`.

## Legacy Tables

| Table | Status | Action |
|-------|--------|--------|
| `carts` | Unreachable from public site | Schedule removal |
| `cart_items` | Unreachable from public site | Schedule removal |

## Index Review

- `quote_requests`: indexed on `status`, `email`, `created_at`.
- Future indexes may be needed on `priority` and `expected_close_date` if heavily filtered.

## Recommendations

- Run migrations on a fresh database before release.
- Execute the commerce cleanup plan to drop obsolete tables.
- Review foreign-key cascade behaviour for distributor-related tables.
