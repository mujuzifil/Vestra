# Phase 24.11 — Production Hardening (v2.1.0)

**Branch:** `feature/stage24-11-v2.1.0-hardening`  
**Stage:** 24.11 · **Release:** v2.1.0  
**Scope:** W3–W9 cross-cutting hardening (no deploy)

## Waves covered

| Wave | Focus | Outcome |
|------|--------|---------|
| W3 | Data integrity | Quote ↔ company profile linkage enforced; account portal quote scope fixed; company API mass-assignment stripped (D-001–D-003) |
| W4 | API / validation | Public distributor list query trimmed (D-006); notification template keys verified |
| W5 | Security / RBAC | Stub admin pages gated (D-004); staff assignee lists exclude customers (D-010); support ticket create authorized (D-011); `Gate::authorize` on Companies/Quotes mutating actions verified |
| W6 | Performance | Quote list eager-load diet (D-008); support avg-resolution DB aggregate (D-009); list indexes on `company_profiles.status`, `support_tickets.status`, `quote_requests(status, created_at)` |
| W7 | Notifications | Template keys `quote_request.*` aligned with listener/seeder (audit note, no code change) |
| W8 | Media | Usage rows cleaned on product/blog delete (D-012); hard delete still blocked when usages exist |
| W9 | RBAC policies | `SupportTicketPolicy::create` added; `User::scopeAssignableStaff()` for admin dropdowns |

## Key code touchpoints

- `backend/app/Services/Admin/QuoteAdminService.php` — list `with()` tuned
- `backend/app/Services/Admin/SupportAdminService.php` — KPI aggregate + assignee query
- `backend/app/Services/Admin/CompanyService.php` — account manager filter query
- `backend/app/Models/User.php` — `scopeAssignableStaff()`
- `backend/app/Models/Product.php`, `BlogPost.php` — delete hooks for `media_asset_usages`
- `backend/app/Policies/SupportTicketPolicy.php` — `create()`
- `backend/app/Filament/Pages/Sales/CompaniesPage.php` — ticket create gate
- `backend/database/migrations/2026_08_06_120000_add_stage24_11_list_performance_indexes.php`

## Tests reviewed

- `tests/Feature/AuthenticationSecurityTest.php` — login/register rate limits, token lifecycle, session config
- `tests/Feature/AuthorizationSecurityTest.php` — mass assignment, IDOR, admin endpoint denial, audit on denial
- `tests/Feature/Admin/QuotesPageTest.php`, `CompaniesPageTest.php` — page access and CRUD flows

Run full suite before gate sign-off:

```powershell
cd backend
php artisan test
```

## Release artifacts

- [STAGE24_11_DEFECT_BACKLOG.md](../../../docs/release/STAGE24_11_DEFECT_BACKLOG.md)
- [STAGE24_11_RELEASE_GATE.md](../../../docs/release/STAGE24_11_RELEASE_GATE.md)
- [STAGE24_11_AUDIT_MATRIX.md](../../../docs/release/STAGE24_11_AUDIT_MATRIX.md)
- [KNOWN_ISSUES.md](../../../docs/release/KNOWN_ISSUES.md) — KI-001 reassessed; residual-risk acceptance on gate item 11

## Open items (not closed in repo)

| ID | Severity | Summary |
|----|----------|---------|
| KI-001 | Critical (external) | Credential rotation + history purge — operator action required before go-live |
| KI-002 | Medium | Pint backlog (CI advisory) |
| KI-003 | Medium | No PHPStan/Larastan yet |
| KI-004 | Medium | Frontend exhaustive-deps warnings |
| KI-005 | Medium | Rehearsal not on target Linux VPS |

No Critical/High **application** defects remain open in the Stage 24.11 backlog after D-001–D-012 fixes.
