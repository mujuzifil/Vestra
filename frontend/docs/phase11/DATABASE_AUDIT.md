# Phase 11 — Database Audit (Frontend Perspective)

## Scope

Frontend-facing data models and schema concerns that affect the public website.

## Findings

1. **Quote Requests**
   - The `QuoteRequestService` was writing `requirements` that had no corresponding column.
   - Fixed by adding migration `2026_08_01_090000_add_requirements_to_quote_requests_table.php` and adding `requirements` to `$fillable`.

2. **Legacy Commerce Tables**
   - `carts` and `cart_items` remain in the database.
   - They are not reachable from the public B2B website.
   - Removal is deferred to the backend commerce cleanup phase.

## Recommendations

- Schedule removal of `carts`/`cart_items` once the commerce cleanup plan is executed.
- Verify all migrations run cleanly on a fresh database before release.
