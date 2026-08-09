# Stage 24.13A — Conversion & Distributor Locator Audit

**Status:** AUDIT ONLY — no application, schema, route, API, UI, config, data, or production changes  
**Audit date:** 2026-08-09  
**Sources:** repository `origin/master` (`F:\vestra-wt-integrate`) + live production (`/opt/vestra`, HTTPS)

---

## 1. Executive Summary

### What already works

- **Primary navigation (8 items)** including Where to Buy — live HTTP 200; structure must be preserved.
- **Homepage hero CTAs** for **Request a Quote** → `/request-quote` and **Become a Distributor** → `/distributor`.
- Mid-page conversion sections: `RequestQuoteSection`, `DistributorCtaSection`, `ContactBannerSection`.
- Destination pages `/request-quote`, `/distributor`, `/where-to-buy` all return **HTTP 200**.
- **Where to Buy** already includes a **Distributor Directory** with search (name) + district + region filters, wired to `GET /api/v1/public/distributors`.
- Public distributor **stats** and **coverage** APIs exist.
- Backend **Distributor** domain is mature: company, branches (district/city/lat/long), contacts, service areas, operating hours JSON, Active Partners admin, Territories/Branches admin, Feature tests for public listing lifecycle.
- Quote and distributor application APIs + Feature tests exist.

### What is missing

- Homepage **Where to Buy** CTA (hero or post-hero) — **E** on homepage.
- **Silver / Gold / Master** distributor categories — **E** (no schema/enum/API/UI).
- Public **Silver/Gold/Master badges** — **E**.
- Filter by distributor **category/tier** — **E**.
- Dedicated **WhatsApp** field/button on public cards — **E** (site-level WhatsApp exists on Need Help; not per-distributor).
- Public **Google Maps** link/button despite branch lat/long in API — **D** (data exists; UI unused).
- **Stock availability** (In Stock / Low Stock / Out of Stock) for public locator — **E**.
- Exact search heading copy *“Find an Authorized VESTRA Distributor Near You.”* — **C** (similar heading exists; different wording).
- Live production currently returns **zero** public distributors / zero stats from DB-backed counts (empty locator results).

### What is broken / weak

- Live locator is **functionally empty** (`data: []`, stats all `0`) — infrastructure works; **no ACTIVE published partners** visible via public API as of audit. Classify locator usefulness as **C/B** depending on whether empty is expected (no seeded partners) vs unintended.

### Reuse vs enhance vs new

| Item | Action |
|------|--------|
| Nav + footer | **Preserve** |
| Hero Quote / Distributor CTAs | **Reuse**; optionally regroup with third CTA |
| Homepage Where to Buy CTA | **New additive** link to existing `/where-to-buy` |
| `/where-to-buy` page + `DirectoryList` | **Enhance**, do not rebuild |
| Public distributor APIs | **Extend** filters/fields; do not replace |
| Distributor / Branch models | **Extend** for tier, WhatsApp, stock, maps UX as needed |
| Silver/Gold/Master + stock | **New** schema/API/admin/UI |
| Quote / Distributor destination pages | **Reuse** |

---

## 2. Correct Production Baseline

| Item | Verified value | Evidence |
|------|----------------|----------|
| Production commit | **`5443b07`** | `git rev-parse` on `/opt/vestra` |
| Tag ancestry | **`v2.1.0-31-g5443b07`** | `git describe --tags --always` |
| Production images | **`local-20260807093405`** (frontend + backend) | `docker compose … images` |
| Repository `origin/master` | **`5443b07`** | Local worktree matches prod tip |
| Stage 24.11 tip `a9e9c96` | Historical ancestor only | Stage 24.13 audit + git ancestry |
| Live routes | `/`, `/request-quote`, `/distributor`, `/where-to-buy` → **HTTP 200** | Production curl 2026-08-09 |
| Public API | `/api/v1/public/distributors` → `{"success":true,"data":[]}` | Live curl |
| Public stats | all zeros | Live curl |

**Divergence from Stage 24.11 claimed tip:** confirmed; implement against **`5443b07` / `local-20260807093405`**.

---

## 3. Preservation / No-Removal Matrix

| Existing Capability | Current State | Preserve? | Reuse? | Action |
|---------------------|---------------|-----------|--------|--------|
| Primary nav (8 items) | A — live 200 | Yes | Yes | Do not remove/replace |
| Footer quick links | A | Yes | Yes | Keep |
| Hero Quote CTA | A | Yes | Yes | Keep; may regroup |
| Hero Distributor CTA | A | Yes | Yes | Keep; may regroup |
| Mid-page Quote/Distributor sections | A | Yes | Yes | Keep |
| `/request-quote` + `POST /quote-requests` | A | Yes | Yes | Keep |
| `/distributor` public apply + portal prefix | A / C (dual-use) | Yes | Yes | Careful path hygiene |
| `/where-to-buy` page | A (page) / C (empty data, gaps) | Yes | Yes | Enhance locator |
| `DirectoryList` + public API filters | A infrastructure | Yes | Yes | Extend |
| Active Partners admin | A (partial field coverage) | Yes | Yes | Extend for new fields |
| Branches / Territories admin | A | Yes | Yes | Extend maps/WhatsApp UX |
| Distributor price tiers (qty pricing) | A — **not** Silver/Gold/Master | Yes | No for badges | Do not confuse with category tiers |

---

## 4. Homepage Conversion Audit

Desired pattern: nav kept; hero kept; three actions visible without opening the menu: Quote, Distributor, Where to Buy.

### 4.1 Request a Quote

| Question | Evidence | Answer |
|----------|----------|--------|
| Homepage CTA? | `hero-section.tsx` L88–90 | **Yes** — hero primary |
| Dedicated section? | `RequestQuoteSection` (`id="request-quote"`) | **Yes** |
| Also | Featured products cards; ContactBanner | Yes |
| Routes to `/request-quote`? | Link href | **Yes** |
| Destination works? | Live HTTP 200 | **Yes** |
| Form API | `POST /api/v1/quote-requests` (`lib/api/quote-requests.ts`) | **Yes** |
| Tests | `QuoteRequestControllerTest.php` | **Yes** |
| Prominent? | Gradient primary hero button + mid-page section | **Yes** |
| Reusable in trio? | **Yes** | Keep destination; reuse button styling/track |

**Classification:** **A** (exists/works) · **C** only if equal-weight three-CTA cluster is required visually.

### 4.2 Become a Distributor

| Question | Evidence | Answer |
|----------|----------|--------|
| Homepage CTA? | Hero secondary L91–93 | **Yes** |
| Mid-page? | `DistributorCtaSection` “Apply Now” | **Yes** |
| Contact banner? | Yes | Yes |
| Routes to `/distributor`? | Yes | **Yes** |
| Public apply works? | Page 200 + `POST /api/v1/distributor` + form | **A** (page/API); full submit not exercised live (audit rule: no real form posts) |
| Portal dual-use? | `/distributor` marketing vs `/distributor/dashboard` portal | **Yes** — homepage CTA to public apply is correct; do not change portal routes |
| Accidental portal interference? | Low if only adding homepage links | Safe if href stays `/distributor` |
| Reusable? | **Yes** | |

**Classification:** **A** · **C** for trio visual parity.

### 4.3 Where to Buy

| Question | Evidence | Answer |
|----------|----------|--------|
| Homepage CTA? | Grep `components/sections` for `where-to-buy` | **No** — **E** on homepage |
| Nav? | `navbar.tsx` `navLinks` | **Yes** — **A** |
| Footer? | `footer.tsx` `quickLinks` | **Yes** — **A** |
| `/where-to-buy` exists? | `app/where-to-buy/page.tsx` | **Yes** |
| Live HTTP? | 200 | **Yes** |
| Public distributors API? | `PublicDistributorController` + routes | **Yes** |
| Displays distributor data? | `DirectoryList` wired; live `data: []` | **D** — UI ready, live list empty |
| Reuse route for homepage CTA? | **Yes** — link to `/where-to-buy` or `#directory` on that page | |

**Classification:** Homepage CTA **E** · Destination/page **A** · Live directory content **C** (empty) / **F** for “should there be partners?” without DB dump of non-active rows.

---

## 5. Homepage CTA Placement Audit

### Current layout

- **Desktop:** Hero copy left / product visual right; CTA row = 2 buttons (`flex-wrap gap-4`).
- **Mobile:** Stacked hero; CTAs wrap; site-wide `overflow-x-clip` already applied.
- **Tracking:** `data-track="hero-primary-cta"`, `hero-secondary-cta`, plus section tracks.
- **A11y:** Buttons via shared `Button` + links; hero `aria-labelledby`.

### Desired visitor behavior

Visitor must see Quote, Distributor, and Where to Buy **without opening nav**.

### Options evaluated

| Option | Pros | Cons | Fit |
|--------|------|------|-----|
| **Extend existing hero CTA row** (add 3rd button) | Minimal change; highest prominence; reuses pattern/tracks | Three buttons on small screens need careful wrap/stack | **Recommended** |
| Immediate post-hero CTA group | Preserves exact 2-button hero look | Adds section; slightly lower “immediate” impact | Acceptable alternative |
| Rebuild hero | Violates preserve rule | Overkill | **Reject** |

**Recommendation:** **Additive extension of the existing hero CTA row** to include Where to Buy (outline or tertiary style so Quote remains primary). On mobile, keep `flex-wrap` / full-width stacking already used by product cards. Do **not** remove mid-page Quote/Distributor sections. Do **not** change nav.

---

## 6. Where to Buy Audit

### Current page architecture

| Piece | Path |
|-------|------|
| Page | `frontend/app/where-to-buy/page.tsx` |
| Client | `where-to-buy-page-client.tsx` |
| Hero | `_components/where-to-buy-hero.tsx` — “Find VESTRA® Products Near You” / “Distributor Locator” |
| Directory | `_components/directory-section.tsx` → `components/distributor/directory-list.tsx` |
| Stats | `network-stats-section` + settings fallbacks |
| Coverage | `coverage-section` |
| CTAs | Become distributor, related resources, final CTA |

### APIs

| Endpoint | Role | Live |
|----------|------|------|
| `GET /api/v1/public/distributors` | List ACTIVE partners; filters `search`, `district`, `region` | `data: []` |
| `GET /api/v1/public/distributors/stats` | Counts | all `0` |
| `GET /api/v1/public/distributors/coverage` | Region/district groups | `[]` |
| `GET /api/v1/public/distributors/{id}` | Detail | Not smoke-tested with empty set |

**Controller:** `backend/app/Http/Controllers/Api/V1/PublicDistributorController.php`  
**Resource:** `PublicDistributorResource` + `PublicDistributorBranchResource`  
**Filter rule:** `status = active` only  

### Tests

- `backend/tests/Feature/Admin/DistributorLifecycleTest.php` — asserts public listing behavior around lifecycle  
- `backend/tests/Feature/Admin/ActivePartnersPageTest.php` — public distributors assertions  

### Trust messaging

- Copy references **authorised** distributors/partners (hero, FAQs, directory empty-state, directory subtitle).
- No verification badge component beyond marketing copy + optional `business_type` chip.
- Admin authorization is effectively **Active vs Suspended** account status (and application approval lifecycle), not Silver/Gold/Master.

**Page classification:** **A** structure · **C** vs premium locator requirements · **C/B** live emptiness.

---

## 7. Distributor Data Model Audit

### Field matrix (desired public profile)

| Field | Exists DB | Exists API (public) | Exists Admin | Exists Public UI | Functional | Classification |
|-------|-----------|---------------------|--------------|------------------|------------|----------------|
| Business name | Yes (`company_name` / `trading_name`) | Yes | Yes | Yes | Yes | **A** |
| Distributor badge Silver/Gold/Master | **No** | **No** | **No** | **No** | N/A | **E** |
| Account status Active/Suspended | Yes | Implicit (filter only) | Yes | No badge for tier | Yes for listing gate | **A** (status) ≠ tier |
| District | Yes (distributor + branch + service_areas) | Yes | Yes (branches/territories) | Partial (address/filters) | Yes | **A** / **C** area UX |
| Area / town / city | Yes (`city`) | Yes | Yes | Via formatted address | Yes | **A** |
| Opening hours | Yes (`operating_hours_json`) | Yes | Limited on slim Distributor form | Yes if data present | **D** admin UX | **D** |
| Phone | Yes | Yes | Yes | Yes | Yes | **A** |
| WhatsApp (dedicated) | **No** dedicated column | **No** | **No** | Site-level only (`NeedHelpSection`) | Partial | **E** per partner · **A** site WA |
| Google Maps location | Lat/long on **branches** | Lat/long in branch resource | Yes (branch form) | **Not rendered** as Maps link | Data **D** | **D** |
| Stock In/Low/Out | **No** | **No** | **No** | **No** | N/A | **E** |
| Service areas | Yes | Yes | Via service areas model/admin paths | Chips | Yes | **A** |
| Logo | Yes | `logo_url` | Partial | Not shown in DirectoryList cards | **D** | **D** |

### Categories Silver / Gold / Master

| Layer | Finding | Class |
|-------|---------|-------|
| Schema | No tier/category enum on `distributors` | **E** |
| `distributor_price_tiers` | Quantity price breaks per product — **not** partner badges | **A** wrong concept for this requirement |
| `DistributorAccountStatus` | `active` / `suspended` only | **A** for account lifecycle |
| Admin | No Silver/Gold/Master controls | **E** |
| Public API/UI | No | **E** |

**Schema change required?** **Yes** (or equivalent JSON/settings field) to support Silver/Gold/Master as a first-class, admin-managed, filterable attribute. Extending `business_type` free-text is **not** adequate for reliable badges/filters.

### Stock availability

No public-locator stock field on distributors/branches. Product inventory exists elsewhere in the commerce domain but is **not** exposed as In/Low/Out for Where to Buy cards. **New field(s)** required if this remains a locator requirement.

---

## 8. Admin Management Audit

| Capability | Exists? | Where | Notes | Class |
|------------|---------|-------|-------|-------|
| Create distributors | Partial | Onboarding via approved requests / lifecycle | Not a simple “add locator listing” wizard | **D** |
| Edit distributors | Yes | `DistributorResource` + Active Partners | Slim form (company, email, phone, status) | **C** |
| Delete/deactivate | Suspend via status | Active Partners / model `suspend()` | Soft via `suspended` | **A** |
| Set category Silver/Gold/Master | No | — | **E** |
| Set district | Yes | Distributor + branch + service areas | **A** |
| Set area/town | Yes (`city`) | Branches / partner data | **A** |
| Set opening hours | Data model yes | Admin form coverage thin | **D** |
| Set phone | Yes | Partner + branch + contact | **A** |
| Set WhatsApp | No dedicated | — | **E** |
| Set Google Maps / lat-long | Yes on branches | `DistributorBranchResource` / relation manager | **A** data · public UI **D** |
| Set stock availability | No | — | **E** |
| Publish/unpublish | Via Active/Suspended | Public API filters Active only | **A** (binary) |
| Authorization/verification | Application approval lifecycle + account status | Requests + Partners | **A** · not tier badges |
| RBAC | Policies + Gate on ActivePartnersPage | `DistributorPolicy`, etc. | **A** |
| Tests | `ActivePartnersPageTest`, `DistributorLifecycleTest` | — | **A** |

**Admin routes (examples):**

- Filament Active Partners: slug `distributors/active-partners` (`ActivePartnersPage`)
- Territories / Branches: `TerritoriesPage` + `DistributorBranchResource`
- Requests: `DistributorRequestResource`

**Gap:** Admins **cannot** today fully manage the future premium locator (tiers, stock, WhatsApp, rich public profile) **without code changes**. They **can** manage core partner identity, status, branches, districts, lat/long, and contacts.

---

## 9. Search / Filtering Audit

| Capability | Current | Class |
|------------|---------|-------|
| Search by business name | Yes — `search` query → company/trading/contact | **A** |
| Search/filter by district | Yes — `district` query (distributor, service area, default branch) | **A** |
| Search by area/town | Partial — free-text `region` overlaps district/city naming; no dedicated “town” filter | **C** / **D** |
| Filter by Silver/Gold/Master | No | **E** |
| Client vs server | Server-side API filters; client debounce 300ms in `DirectoryList` | **A** |
| Pagination | Public index returns full `get()` collection — no pagination | **C** for scale |
| Query params | `search`, `district`, `region` | **A** |
| Admin data management | Territories/partners support district/region filters | **A** |

---

## 10. Scalability Assessment

**Strengths to build on**

- Normalized distributor graph (company → branches → service areas → contacts).
- Public read API already separated from portal APIs.
- Admin workspace pages for partners/territories.
- Status gate (`active`) for public visibility.

**Required for growth / premium locator**

1. Partner **tier/category** (Silver/Gold/Master) + admin + public badge + filter.  
2. Optional **WhatsApp** (and Maps deep link UX using existing lat/long).  
3. Optional **stock status** if product is locator-scoped (clarify business rule: per-partner? per-SKU?).  
4. **Pagination** (or cursor) on public list when partner count grows.  
5. Seed/activate real **ACTIVE** partners so production directory is non-empty.  
6. Richer admin forms for operating hours / public profile fields without code deploys for content.

---

## 11. Gap Matrix

| Requirement | Existing | Functional | Classification | Reuse | Required Change | Risk |
|-------------|----------|------------|----------------|-------|-----------------|------|
| Keep primary nav | Yes | Yes | **A** | Yes | None | Low |
| Homepage Quote CTA | Yes | Yes | **A** | Yes | Optional regroup | Low |
| Homepage Distributor CTA | Yes | Yes | **A** | Yes | Optional regroup | Low |
| Homepage Where to Buy CTA | No | N/A | **E** | Route yes | Add hero/post-hero link | Low |
| Three-CTA immediate visibility | Partial | Partial | **D** | Yes | Additive CTA | Low |
| `/where-to-buy` destination | Yes | Yes (empty list) | **A**/**C** | Yes | Enhance UI/data | Medium if rebuild temptation |
| Directory search name/district | Yes | Yes | **A** | Yes | Extend filters | Low |
| Category filter Silver/Gold/Master | No | N/A | **E** | No | Schema+API+admin+UI | Medium |
| Public badges | No (only business_type chip) | N/A | **E** | Styling patterns yes | New badge component | Low–Med |
| Opening hours | Yes | Partial | **D** | Yes | Admin UX + data entry | Low |
| Phone | Yes | Yes | **A** | Yes | None | Low |
| WhatsApp button per partner | No | N/A | **E** | Site WA pattern | Field + UI | Low |
| Google Maps | Lat/long yes; UI no | Partial | **D** | Yes | UI link builder | Low |
| Stock In/Low/Out | No | N/A | **E** | Unclear | Schema + rules + UI | Medium–High |
| Trust “authorized” messaging | Copy yes | Yes | **A**/**C** | Yes | Optional badge | Low |
| Admin manage without code | Partial | Partial | **D** | Yes | Extend admin for new fields | Medium |
| Live public partners populated | API empty | Empty | **C**/**B*** | Yes | Data/ops activate partners | Medium |

\*Treat empty live list as **ops/data** unless investigation shows ACTIVE partners wrongly excluded.

---

## 12. Recommended Implementation Sequence

**Do not implement in this stage.** Suggested order based on evidence:

1. **Homepage CTA enhancement** — add Where to Buy to hero CTA row (additive); preserve nav and existing sections.  
2. **Confirm/activate public partner data** — ensure ≥1 ACTIVE distributor appears in production public API (ops/admin).  
3. **Where to Buy IA / copy polish** — heading/trust messaging; keep `DirectoryList`.  
4. **Public Maps + WhatsApp UX** — use existing lat/long; add WhatsApp field if required.  
5. **Distributor tier model (Silver/Gold/Master)** — migration, enum, admin, public resource, badges, filter.  
6. **Stock availability** — only after product owner defines scope (partner-level vs SKU-level).  
7. **Pagination / performance** on public list.  
8. **Admin form completeness** (hours, public profile fields).  
9. **Responsive/mobile validation** of hero trio + directory.  
10. **Regression tests** (Feature + smoke) then production deploy.

---

## 13. Explicit Out-of-Scope / Non-Actions (this audit)

This audit did **not**:

- Modify application code  
- Modify database schema or data  
- Modify routes, APIs, UI, or configuration  
- Deploy production  
- Remove existing navigation  
- Remove existing CTA functionality  
- Rebuild working destination pages  
- Submit real quote/distributor forms on production  

---

## 14. Implementation Gate

### **READY WITH CONDITIONS**

Conditions before / during implementation:

1. Treat production baseline as **`5443b07` / `local-20260807093405`**, not Stage 24.11 tip.  
2. **Preserve** all eight primary nav items and existing Quote/Distributor homepage CTAs/sections.  
3. Homepage work is **additive** (hero CTA row extension recommended).  
4. Enhance **`/where-to-buy` + public distributor APIs**; do not greenfield rebuild.  
5. Silver/Gold/Master and stock require **explicit schema/API/admin** work — not available for reuse today.  
6. Resolve **empty live public distributor list** (activate/publish partners or document intentional emptiness) before calling the locator “complete.”  
7. Clarify **stock availability** business rule before modeling.  
8. Avoid colliding `/distributor` public apply with authenticated portal routes.

---

## Appendix — Key evidence paths

- `frontend/app/page.tsx`  
- `frontend/components/sections/hero-section.tsx`  
- `frontend/components/navigation/navbar.tsx`  
- `frontend/components/layout/footer.tsx`  
- `frontend/app/where-to-buy/*`  
- `frontend/components/distributor/directory-list.tsx`  
- `frontend/lib/api/public-distributors.ts`  
- `backend/app/Http/Controllers/Api/V1/PublicDistributorController.php`  
- `backend/app/Http/Resources/V1/PublicDistributorResource.php`  
- `backend/app/Models/Distributor.php`, `DistributorBranch.php`  
- `backend/app/Enums/DistributorAccountStatus.php`  
- `backend/app/Filament/Pages/Distributors/ActivePartnersPage.php`  
- `backend/app/Filament/Resources/DistributorBranchResource.php`  
- `backend/tests/Feature/Admin/DistributorLifecycleTest.php`  
- `backend/tests/Feature/Admin/ActivePartnersPageTest.php`  
