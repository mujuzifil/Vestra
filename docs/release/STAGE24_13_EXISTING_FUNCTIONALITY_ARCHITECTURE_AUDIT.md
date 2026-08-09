# Stage 24.13 — Existing Functionality & Architecture Audit

**Status:** AUDIT ONLY (no application code, schema, route, UI, config, or production changes)  
**Audit date:** 2026-08-08  
**Auditor source of truth:** repository `origin/master` + live production (`/opt/vestra`)  
**Scope covered:** §§1–7 of Stage 24.13 (public website, navigation, homepage conversion pattern)

---

## Classification legend

| Code | Meaning |
|------|---------|
| **A** | Exists and works |
| **B** | Exists but is broken |
| **C** | Exists but requires enhancement |
| **D** | Partially exists |
| **E** | Does not exist |
| **F** | Cannot be verified |

Every classification below includes evidence.

---

## 1. Production baseline (corrected)

### Claimed baseline (Stage brief)

| Item | Claimed |
|------|---------|
| Release | `v2.1.0` |
| Stage | `24.11` |
| Production tip | `a9e9c96` |
| App image | `local-20260806140735` |
| Rollback image | `local-20260806111626` |

### Verified live baseline (2026-08-08)

| Item | Evidence |
|------|----------|
| Production tip | **`5443b07`** — `Merge branch 'develop' for account photo upload fix` (`git rev-parse` on `/opt/vestra`) |
| Tag ancestry | `git describe` → **`v2.1.0-31-g5443b07`** (31 commits after `v2.1.0`) |
| Claimed tip | `a9e9c96` = `docs(release): Stage 24.11 v2.1.0 production deployment report` — **ancestor** of current master (`merge-base --is-ancestor` exit 0) but **not** current tip |
| Frontend/backend image | **`local-20260807093405`** (compose images on production) |
| Containers | backend, frontend, nginx, queue, scheduler, db, redis, certbot — all up |
| Repo tip | Local/worktree `origin/master` = `5443b07` (matches production) |

**Classification:** **C** for “treat claimed tip as current production” — the Stage 24.11 tip is historical; production has advanced through Stages 24.12 / 24.12A and subsequent mobile/account fixes.

**Implication for later stages:** Implement against **`5443b07` / `local-20260807093405`**, not `a9e9c96`. Do not roll back to Stage 24.11 tip without an explicit decision.

Commits after `a9e9c96` include (non-exhaustive): Stage 24.12 UX, 24.12A prod fixes, admin activity fixes, favicon, mobile nav contrast/scroll, mobile overflow, product/account mobile, account photo upload.

---

## 2. Critical audit rule — reuse posture

| Area | Reuse decision | Class | Evidence |
|------|----------------|-------|----------|
| Primary nav (8 items) | **Keep** — do not remove/replace | **A** | `frontend/components/navigation/navbar.tsx` `navLinks`; live HTTP 200 for all targets |
| Footer quick links | **Keep** | **A** | `frontend/components/layout/footer.tsx` `quickLinks` |
| Homepage hero (existing) | **Keep structure**; may enhance CTAs | **C** vs new conversion pattern | Hero already has Quote + Distributor; missing Where to Buy |
| Mid-page Quote / Distributor sections | **Keep**; do not rebuild | **A** | `RequestQuoteSection`, `DistributorCtaSection` |
| Target pages `/request-quote`, `/distributor`, `/where-to-buy` | **Reuse routes** | **A** | Live 200; pages + APIs present |
| New hero “three CTA” conversion block | **Add/enhance only if required** — do not replace nav | **D/E** on homepage for Where to Buy | No homepage section links to `/where-to-buy` (grep of `components/sections`) |

---

## 3–4. Public website inventory

Live smoke (HTTPS `vestradetergents.com`, 2026-08-08): all listed routes **HTTP 200**. API health **200**; `/api/v1/products` returns product JSON.

### 4.1 Homepage `/`

| Field | Finding |
|-------|---------|
| Route | `/` |
| Page | `frontend/app/page.tsx` |
| Sections | `HeroSection`, `WhyChooseSection`, `ProductCategoriesSection`, `IndustriesSection`, `FeaturedProductsSection`, `ManufacturingSection`, `DistributorCtaSection`, `RequestQuoteSection`, `TestimonialsSection`, `LatestArticlesSection`, `ContactBannerSection` |
| API | Featured products API; blog homepage posts (`getHomepagePosts`); settings/contact fallbacks as used by child sections |
| Data | Mixed: static marketing copy + dynamic products/articles |
| Tests | No dedicated frontend homepage E2E found in this pass; backend product/blog APIs covered elsewhere |
| Production | **200** |
| Class | **A** (page works) / **C** (conversion pattern vs reference — see §6–7) |
| Safe to extend | **Yes** — add CTAs/sections without removing nav or existing sections |

### 4.2 About Us `/about`

| Field | Finding |
|-------|---------|
| Page | `frontend/app/about/page.tsx` → `about-page-client.tsx` |
| Data | Largely static marketing; may use settings |
| Production | **200** |
| Class | **A** |
| Safe to extend | **Yes** |

### 4.3 Products `/products`

| Field | Finding |
|-------|---------|
| Page | `frontend/app/products/page.tsx` |
| API | `GET /api/v1/products`, categories |
| DB | `products`, `categories` |
| Data | Dynamic |
| Production | **200** + API products payload |
| Class | **A** |
| Safe to extend | **Yes** |

### 4.4 Product details `/products/[slug]`

| Field | Finding |
|-------|---------|
| Page | `frontend/app/products/[slug]/page.tsx` → `product-page-client.tsx` |
| API | `GET /products/{slug}`, reviews, recommendations |
| Production | **200** (`/products/heavy-duty-detergent`) |
| Class | **A** (post mobile-fit fixes on `e0a7d00`) |
| Safe to extend | **Yes** |

### 4.5 Request a Quote `/request-quote`

| Field | Finding |
|-------|---------|
| Page | `frontend/app/request-quote/page.tsx` → `request-quote-page-client.tsx` |
| API | `POST /api/v1/quote-requests` (`frontend/lib/api/quote-requests.ts`) |
| Tests | `backend/tests/Feature/Api/V1/QuoteRequestControllerTest.php` |
| Production | **200** |
| Class | **A** |
| Safe to extend | **Yes** — reuse form/API; do not rebuild |

### 4.6 Become a Distributor `/distributor`

| Field | Finding |
|-------|---------|
| Page | `frontend/app/distributor/page.tsx` (public application marketing + `DistributorForm`) |
| Note | Same path prefix hosts authenticated portal under `/distributor/dashboard` etc. |
| API | `POST /api/v1/distributor`; status via portal application-status when logged in |
| Production | **200** |
| Class | **A** (public apply flow) |
| Safe to extend | **Yes** — careful not to break portal routes |

### 4.7 Where to Buy `/where-to-buy`

| Field | Finding |
|-------|---------|
| Page | `frontend/app/where-to-buy/page.tsx` → `where-to-buy-page-client.tsx` |
| API | `GET /public/distributors/stats` (+ settings); public distributors API exists |
| Production | **200** |
| Class | **A** (page exists and loads) / **C** if denser locator UX is later required |
| Safe to extend | **Yes** |

### 4.8 Blog `/blog`, `/blog/[slug]`

| Field | Finding |
|-------|---------|
| Pages | `frontend/app/blog/page.tsx`, `blog/[slug]/page.tsx` |
| API | `/blog/posts`, featured, homepage, categories, tags |
| Production | **200** (`/blog`) |
| Class | **A** |
| Safe to extend | **Yes** |

### 4.9 Contact `/contact`

| Field | Finding |
|-------|---------|
| Page | `frontend/app/contact/page.tsx` → `contact-page-client.tsx` |
| API | `POST /api/v1/contact` |
| Production | **200** |
| Class | **A** |
| Safe to extend | **Yes** |

### 4.10 Search

| Field | Finding |
|-------|---------|
| UI | Navbar search (`navbar.tsx` + `useSearchSuggestions`) |
| API | `GET /search/autocomplete`, `/search/popular` |
| Tests | `backend/tests/Feature/Api/V1/SearchControllerTest.php` |
| Class | **A** (API + UI present) / **F** for full UX quality without interactive session |
| Safe to extend | **Yes** |

### 4.11 Authentication / account

| Field | Finding |
|-------|---------|
| Routes | `/auth/login`, `/auth/register`, `/account/*` |
| API | `/auth/*` Sanctum routes in `backend/routes/api.php` |
| Production | `/auth/login` **200** |
| Recent | Photo upload restored (`3e1a857`) — `/account/profile/photo` |
| Class | **A** for core login/account shell; portal features vary by role |
| Safe to extend | **Yes** with auth/role care |

### 4.12 Header / Footer

| Field | Finding |
|-------|---------|
| Header | `Navbar` in `root-layout-client.tsx` — fixed 72px, desktop + mobile drawer |
| Footer | `Footer` — quick links mirror primary nav; contact from settings with static fallbacks |
| Class | **A** |
| Safe to extend | **Yes** — **do not remove nav items** |

---

## 5. Navigation audit

Source of truth: `frontend/components/navigation/navbar.tsx` lines 15–33.

| Nav item | Href | Exists | Routes | Live load | Mobile | Dependencies | Class |
|----------|------|--------|--------|-----------|--------|--------------|-------|
| Home | `/` | Yes | Yes | 200 | Same `navLinks` in mobile drawer | Layout | **A** |
| About Us | `/about` | Yes | Yes | 200 | Yes | About client | **A** |
| Products | `/products` (+ dropdown SKUs) | Yes | Yes | 200 | Parent link in mobile (children also listed) | Products API | **A** |
| Become a Distributor | `/distributor` | Yes | Yes | 200 | Yes | Distributor form/API | **A** |
| Request a Quote | `/request-quote` | Yes | Yes | 200 | Yes | Quote API | **A** |
| Where to Buy | `/where-to-buy` | Yes | Yes | 200 | Yes | Public distributors/settings | **A** |
| Blog | `/blog` | Yes | Yes | 200 | Yes | Blog API | **A** |
| Contact | `/contact` | Yes | Yes | 200 | Yes | Contact API | **A** |

**Also present in header (not primary text nav):**

| Control | Status | Class |
|---------|--------|-------|
| Search | Exists (icon + overlay/suggestions) | **A** / **F** full UX |
| Notifications | `NotificationBell` when authenticated | **A** / **F** without logged-in session |
| Account / Login | Login icon → `/auth/login`; user menu when auth | **A** |
| Active state | `isActive(href)` + underline on desktop | **A** |
| Mobile nav | Full-viewport drawer, scroll lock, close control (post `8b65803`) | **A** |

**Constraint for next stages:** New homepage CTAs must **not** remove or replace these eight nav items.

---

## 6. Homepage conversion audit

### 6.1 Hero structure (current)

| Element | Evidence |
|---------|----------|
| Layout | Full-bleed navy hero; 2-column desktop (copy + product visual); stacked mobile (`hero-section.tsx`) |
| Badge | “PROFESSIONAL CLEANING SOLUTIONS” |
| H1 | “Professional Cleaning Solutions Manufactured for Uganda.” |
| Body | B2B manufacturer positioning copy |
| Feature strip | Manufactured in Uganda / Professional Quality / Advanced Formulations |
| Primary CTA | **Request a Quote** → `/request-quote` (`data-track="hero-primary-cta"`) |
| Secondary CTA | **Become a Distributor** → `/distributor` (`data-track="hero-secondary-cta"`) |
| Where to Buy in hero | **Absent** |

### 6.2 Other homepage conversion surfaces

| Surface | Quote | Distributor | Where to Buy | Prominence |
|---------|-------|-------------|--------------|------------|
| Hero | Yes | Yes | **No** | High |
| `DistributorCtaSection` | No | Yes (“Apply Now”) | No | Mid-page high |
| `RequestQuoteSection` | Yes | No | No | Mid-page high |
| `FeaturedProductsSection` | Per-card quote links | No | No | Medium |
| `ContactBannerSection` | Yes | Yes | No (+ Contact Sales) | Lower page |
| `ProductCategoriesSection` | → `/products` | No | No | Medium |
| Entire `components/sections/*` | — | — | **No `/where-to-buy` matches** | — |

### 6.3 Action-by-action (homepage)

#### Request a Quote

| Question | Answer |
|----------|--------|
| Exists on homepage? | **Yes** |
| Where? | Hero (primary), RequestQuoteSection, Featured product cards, ContactBanner |
| Prominence | High (hero primary + dedicated section) |
| Target | `/request-quote` |
| Link works? | Live page **200**; API `POST /quote-requests` + Feature tests |
| Reuse? | **Yes** — keep links/sections; enhance placement if needed |
| New prominent CTA required? | **Optional** — already prominent; may only need visual grouping with siblings |

**Class:** **A** (exists/works) / **C** if reference demands a three-equal CTA cluster in hero

#### Become a Distributor

| Question | Answer |
|----------|--------|
| Exists on homepage? | **Yes** |
| Where? | Hero (secondary), DistributorCtaSection, ContactBanner |
| Prominence | High |
| Target | `/distributor` |
| Link works? | Live **200** |
| Reuse? | **Yes** |
| New prominent CTA required? | **Optional** — same as Quote |

**Class:** **A** / **C** for equal-weight trio pattern

#### Where to Buy

| Question | Answer |
|----------|--------|
| Exists on homepage? | **No** (nav + footer only) |
| Where on site? | Navbar, footer quick links, dedicated `/where-to-buy` page |
| Homepage prominence | **None** |
| Target | `/where-to-buy` |
| Link works (when used)? | Live **200** |
| Reuse? | **Reuse route/page**; add homepage CTA pointing to it |
| New prominent CTA required? | **Yes** — if Stage goal is the reference conversion pattern (three CTAs under hero) |

**Class:** **E** on homepage · **A** as site capability (nav/page)

---

## 7. Homepage reference requirement (conversion pattern)

Desired conceptual pattern:

```text
Existing VESTRA Navigation          → KEEP (Class A)
        |
Existing Homepage Hero              → KEEP structure
        |
        +-- Request a Quote         → EXISTS (hero + sections)
        +-- Become a Distributor    → EXISTS (hero + sections)
        +-- Where to Buy            → MISSING on homepage
        |
Existing Homepage Content           → KEEP
```

| Requirement element | Class | Evidence / next-stage guidance |
|---------------------|-------|--------------------------------|
| Preserve existing navigation | **A** | Do not replace `navLinks` |
| Preserve existing hero brand/layout | **A** / **C** | Extend CTAs; avoid full hero rebuild |
| Three conversion actions in/near hero | **D** | Quote + Distributor present; Where to Buy absent |
| Deep-link to working destination pages | **A** | All three destinations live |
| Do not rebuild Quote/Distributor pages | **A** | Forms + APIs + tests exist |

**Recommended implementation posture for later stages (not executed here):**

1. Keep navbar and footer as-is.  
2. Keep existing mid-page Quote/Distributor sections.  
3. Enhance hero (or immediate post-hero CTA group) to include **Where to Buy** alongside the two existing CTAs, matching the reference *pattern* without copying foreign branding.  
4. Reuse `/where-to-buy`, `/request-quote`, `/distributor` — no new destination pages required for this pattern alone.

---

## Cross-cutting known defects / notes (evidence-backed, non-blocking for this audit)

| Note | Class | Evidence |
|------|-------|----------|
| Production tip ≠ Stage 24.11 tip | **C** (process/docs) | `5443b07` vs claimed `a9e9c96` |
| Deploy script frontend health race | **F**/ops known | Historical deploys exit non-zero then self-heal; containers currently healthy |
| `/distributor` dual use (public apply + portal) | **C** | Same URL prefix; extend carefully |
| Homepage Where to Buy gap | **E** on homepage | Section grep empty |

---

## Safe extension matrix (summary)

| Capability | Class | Extend by |
|------------|-------|-----------|
| Primary navigation | **A** | Additive only |
| Hero Quote / Distributor CTAs | **A** | Reuse; optionally regroup |
| Hero/home Where to Buy CTA | **E** | Add link to existing page |
| Quote form + API | **A** | Reuse |
| Distributor apply + API | **A** | Reuse |
| Where to Buy page + public distributors API | **A** | Reuse / enhance locator later if needed |
| Products / Blog / Contact / About | **A** | Reuse |

---

## Out of scope / truncated brief

The user brief was cut off after §7. This audit covers §§1–7 (baseline, public website, navigation, homepage conversion). Further Stage 24.13 sections (if provided) should be audited in a follow-on pass under the same **AUDIT-ONLY** rules.

---

## Explicit non-actions (this stage)

- No application code changes  
- No schema / route / UI / config / production / deploy changes  
- No new homepage CTAs implemented  
- No navigation items removed or replaced  

**Next stage gate:** Implementation may proceed only after stakeholders accept this baseline, especially production tip **`5443b07`** and the homepage **Where to Buy** gap (**E** on homepage / **A** elsewhere).
