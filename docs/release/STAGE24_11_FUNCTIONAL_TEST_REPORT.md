# Stage 24.11 — Functional & Regression Reports (v2.1.0)

## Functional testing

Admin modules (Companies, Quotes, Applications, Partners, Territories, Credit, Products, Categories, Media, Blog, Support, Feedback, Enquiries, Staff, Roles, Profile, Activity, Tasks) and public surfaces (auth, account, quote, distributor, where-to-buy, products, blog, contact, notifications) marked **Pass** in [STAGE24_11_AUDIT_MATRIX.md](STAGE24_11_AUDIT_MATRIX.md).

Hidden stubs Pipeline/Opportunities: `canAccess(): false` (D-004). Inventory remains gated.

## Regression

| Suite | Result | Notes |
|-------|--------|-------|
| Backend Feature PHPUnit | **630 passed** / 2554 assertions | `phpunit.xml` memory_limit 512M |
| Frontend eslint | Pass (exit 0) | |
| Frontend `npm run build` | Pass | Sitemap AbortSignal timeout (D-023) |

## API / security / performance (summary)

- Authz: Filament `abort_unless` mount (D-014); assignee RBAC (D-010); ticket create gate (D-011)
- API: route order fixes (D-029); review helpful policy (D-028); preference encoding (D-033); admin pagination shape (D-031)
- Perf: list indexes migration; quote/support query diet (D-008/D-009)
- Notifications: template parity + duplicate mail fix (D-018–D-021)
- Media: usage cleanup on delete (D-012)

## Bug resolution

See [STAGE24_11_BUG_RESOLUTION_SUMMARY.md](STAGE24_11_BUG_RESOLUTION_SUMMARY.md) and defect backlog D-001–D-033.
