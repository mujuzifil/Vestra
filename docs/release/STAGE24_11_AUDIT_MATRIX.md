# Stage 24.11 — Audit Matrix

**Stage:** 24.11 · **Release:** v2.1.0  
**Legend:** Pass / Fail / N/A / Pending  
**Updated:** 2026-08-06 (W7, W10–W15 hardening)

## 1. End-to-end workflows

| ID | Workflow | Pass? | Evidence | Notes |
|----|----------|-------|----------|-------|
| E2E-01 | Register/update company → appears in Admin Companies | Pass | `CompanyProfileControllerTest`, `CompaniesPageTest`, `CompanyProfileSyncTest` | D-003 mass-assignment fixed |
| E2E-02 | Public quote → Admin Quotes + company link → status → account portal | Pass | `QuoteRequestControllerTest`, `QuotesPageTest`, `Account/QuoteControllerTest` | D-001/D-002 fixed; D-018 duplicate mail fixed |
| E2E-03 | Distributor apply → review → approve → partner → coverage → credit → where-to-buy | Pass | `ApplicationsPageTest`, `DistributorLifecycleTest`, `ActivePartnersPageTest`, `TerritoriesPageTest`, `CreditPageTest`, `Api/V1/Distributor/PortalTest` | |
| E2E-04 | Blog draft → publish → public blog | Pass | `BlogPageTest` | Public empty state documented Accepted Low |
| E2E-05 | Product create → categories/media → publish → public catalog | Pass | `ProductsPageTest`, `CategoriesPageTest`, `MediaPageTest` | ProductSeeder PNG guard D-017 |

## 2. Admin Portal pages

| Module | Nav | List/CRUD | Filters | Export | Drawers | Pass? |
|--------|-----|-----------|---------|--------|---------|-------|
| Companies | Pass | Pass | Pass | Pass | Pass | Pass |
| Quotes | Pass | Pass | Pass | Pass | Pass | Pass |
| Applications | Pass | Pass | Pass | Pass | Pass | Pass |
| Active Partners | Pass | Pass | Pass | Pass | Pass | Pass |
| Territories | Pass | Pass | Pass | Pass | Pass | Pass |
| Credit | Pass | Pass | Pass | Pass | Pass | Pass |
| Products | Pass | Pass | Pass | Pass | Pass | Pass |
| Categories | Pass | Pass | Pass | Pass | — | Pass |
| Media | Pass | Pass | Pass | Pass | Pass | Pass |
| Blog | Pass | Pass | Pass | Pass | Pass | Pass |
| Support | Pass | Pass | Pass | Pass | Pass | Pass |
| Feedback | Pass | Pass | Pass | Pass | Pass | Pass |
| Enquiries | Pass | Pass | Pass | Pass | Pass | Pass |
| Staff | Pass | Pass | Pass | Pass | Pass | Pass |
| Roles | Pass | Pass | Pass | Pass | Pass | Pass |
| Profile | Pass | Pass | — | — | — | Pass |
| Activity | Pass | Pass | Pass | Pass | — | Pass |
| Tasks | Pass | Pass | Pass | Pass | Pass | Pass |
| Pipeline (hidden) | N/A | N/A | N/A | N/A | N/A | N/A |
| Opportunities (hidden) | N/A | N/A | N/A | N/A | N/A | N/A |
| Inventory (hidden) | N/A | Pass | — | — | — | Pass |

Evidence: `backend/tests/Feature/Admin/*PageTest.php` per module; mount RBAC via `abort_unless(Gate::allows…)` D-014.

## 3. Public Website

| Area | Pass? | Evidence |
|------|-------|----------|
| Auth login/register | Pass | `AuthenticationSecurityTest`, `AccountProfileTest` |
| Account company/quotes/orders/support | Pass | `Account/CompanyProfileControllerTest`, `Account/QuoteControllerTest` |
| Request quote | Pass | `QuoteRequestControllerTest` |
| Distributor apply + portal | Pass | `Distributor/PortalTest`, `ApplicationsPageTest` |
| Where to buy | Pass | `TerritoriesPageTest`, `PublicDistributorController` (D-006) |
| Products / categories | Pass | `ProductsPageTest`, `SearchControllerTest` |
| Blog | Pass | `BlogPageTest`; empty grid Accepted Low — `frontend/docs/phase24_11/README.md` |
| Contact | Pass | `EnquiriesPageTest`, contact mailables verified |
| Notifications | Pass | `NotificationTemplateParityTest`, `NotificationCenterTest`, `QuoteRequestControllerTest` |

## 4. Cross-cutting

| Area | Pass? | Evidence |
|------|-------|----------|
| Data integrity (FKs/orphans) | Pass | D-012 media usage cascade; company/quote FK tests |
| API authz/validation | Pass | `AuthorizationSecurityTest`, policy tests |
| Security / RBAC | Pass | D-010/D-011/D-014; `Gate::allows` on Filament pages |
| Performance (hot lists) | Pass | D-008/D-009; migration `add_stage24_11_list_performance_indexes` |
| Email / notifications | Pass | `NotificationTemplateParityTest`; seeder covers all listener keys; D-018/D-019 |
| Media integrity | Pass | D-012 fixed |
| Responsive / browsers | Pass | Code review — `frontend/docs/phase24_11/README.md` |
| Error handling / activity / KPIs | Pass | Activity/Support/Quotes/Companies/Tasks/Credit KPI tests use DB aggregates |
| Production data hygiene | Pass | `ProductionConfigIntegrityTest`, `ProductSeeder` test guard D-017 |
| Full regression suite | Pending | Operator run required before gate sign-off |
