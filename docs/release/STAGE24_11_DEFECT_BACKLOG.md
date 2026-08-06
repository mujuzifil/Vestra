# Stage 24.11 — Defect Backlog

**Stage:** 24.11 · **Release:** v2.1.0  
**Branch:** `feature/stage24-11-v2.1.0-hardening`  
**Updated:** 2026-08-06

Severity: Critical / High / Medium / Low  
Status: Open / Fixed / Accepted / Won't Fix

| ID | Severity | Module | Summary | Repro | Status |
|----|----------|--------|---------|-------|--------|
| D-001 | High | Quotes / E2E | Public quote submit could persist `QuoteRequest` without `company_profile_id` when profile resolution returned null | POST `/api/v1/quote-requests` with edge-case missing email resolution | Fixed |
| D-002 | Medium | Account / Quotes | Account portal scoped quotes by `user_id` only; quotes linked solely via `company_profile_id` were invisible to the owning customer | Submit guest quote → register → GET `/api/v1/account/quotes` | Fixed |
| D-003 | Medium | CompanyProfile / API | Customer company update used unrestricted mass assignment; privileged fields (`status`, `account_manager_id`) could be injected if validation expanded | PUT `/api/v1/account/company` with `status=active` | Fixed |
| D-004 | Medium | Admin / Hidden pages | Stub Pipeline & Opportunities pages had no `canAccess()` guard if re-registered | Direct Filament route to `/sales/pipeline` or `/sales/opportunities` | Fixed |
| D-005 | — | Admin / Inventory | `InventoryPage` access control verified: route unregistered, `mount()` Gate denies non-admin (no `canAccess()` needed — Filament redirect differs from 403) | Livewire test + route check | Accepted |
| D-006 | Low | Public API | `PublicDistributorController::show` eager-loaded `negotiatedPrices.product` not exposed in public resource (wasted query / leak risk) | GET `/api/v1/public/distributors/{id}` | Fixed |
| D-007 | Low | Applications | `ApplicationsPage` used `distributor()->exists()` causing redundant queries despite list `with('distributor')` | Approve/bulk-approve from applications table | Fixed |
| D-008 | Medium | Quotes / Performance | `QuoteAdminService` list eager-loaded `items.product` and `user.companyProfile` though table only needs `items` + `companyProfile` | Admin Quotes list with line items | Fixed |
| D-009 | Medium | Support / Performance | `SupportAdminService::getAvgResolutionHours()` loaded all resolved tickets into memory | Support KPI card on large ticket volume | Fixed |
| D-010 | Medium | Admin / RBAC | Staff assignee/account-manager queries used `orWhereHas('roles')`, listing storefront `customer` role users in admin dropdowns | Companies/Quotes/Support filter assignee lists | Fixed |
| D-011 | Medium | Companies / RBAC | `CompaniesPage::createSupportTicket()` authorized `view` on company only; missing `create` on `SupportTicket` | Admin creates ticket from company drawer | Fixed |
| D-012 | Medium | Media / Integrity | `media_asset_usages` rows orphaned when owning `Product` or `BlogPost` deleted (usage count drift, stale references) | Delete product/blog still linked in media library | Fixed |
| D-013 | — | Release gate | KI-001 rotation not verifiable from repo; residual-risk acceptance recorded for v2.1.0 gate item 11 | Review STAGE24_11_RELEASE_GATE.md § KI-001 | Accepted |

## Severity policy (ship gate)

- Critical / High: **zero open**
- Medium affecting business workflows: **zero open**
- Low / cosmetic: may remain if accepted in Known Issues with owner

## Related known issues (pre-existing)

See [KNOWN_ISSUES.md](KNOWN_ISSUES.md). Reassess for gate, especially KI-001.

## Audit notes (no defect filed)

- Notification template keys `quote_request.approved|declined|status_changed` match `DispatchNotificationListener` and `NotificationTemplateSeeder` (verified, no change required).
- `CompaniesPage` / `QuotesPage` / `ApplicationsPage` list queries already use appropriate `with()` / `withCount()` in admin services (verified).
