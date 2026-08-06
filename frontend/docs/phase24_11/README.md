# Phase 24.11 — Public Website Audit Notes

**Stage:** 24.11 · **Release:** v2.1.0  
**Branch:** `feature/stage24-11-v2.1.0-hardening`  
**Method:** Static code review + prior Stage 24.x docs (no live browser matrix run in this wave)

## Scope

Public Next.js site under `frontend/app/` — auth, account portal, quote request, distributor apply/portal, where-to-buy, products/categories, blog, contact.

## Responsive layout (code review)

| Check | Pass? | Evidence |
|-------|-------|----------|
| Root layout uses fluid typography (`font-sans`, Poppins, `scroll-smooth`) | Pass | `frontend/app/layout.tsx` |
| Skip link for keyboard users | Pass | `SkipLink` in root layout |
| Header/nav mobile menu pattern | Pass | `RootLayoutClient` + nav components use `lg:` breakpoints |
| Product grid responsive columns | Pass | Product listing/detail use `grid` + `lg:grid-cols-*` |
| Account portal sidebar collapses on small screens | Pass | Account layout uses responsive flex/stack |
| Forms use full-width inputs on mobile | Pass | Shared form components + Tailwind `w-full` |
| Touch targets ≥ 44px on primary CTAs | Pass | Button components use `py-3`/`min-h` patterns |

## Browser compatibility (code review)

| Check | Pass? | Evidence |
|-------|-------|----------|
| No IE-specific APIs | Pass | Next 15 / React 19 stack; no `document.all` etc. |
| CSS uses standard flex/grid (no `-webkit-only` hacks required) | Pass | Tailwind v4 utilities |
| Images use `next/image` or explicit `onerror` fallback | Pass | Product cards, blog hero |
| Client fetches use `fetch` + error boundaries | Pass | API helpers in `frontend/lib/api/` |
| Auth tokens in httpOnly-safe pattern (Sanctum bearer) | Pass | Account API client |

## Error handling (code review)

| Area | Pass? | Evidence |
|------|-------|----------|
| API errors surfaced to user (toast/inline) | Pass | Account + quote forms catch API errors |
| Empty states (no products, no blog, no coverage) | Pass | Conditional copy, not uncaught throws |
| 404 product/blog routes | Pass | Next `notFound()` in page loaders |
| Loading states on client fetches | Pass | `Loader2` spinners on distributor coverage, account lists |

## Intentional stubs / empty states (Accepted Low)

These strings contain “coming soon” but are **data-driven empty states**, not broken navigation:

| Location | Copy | Gate |
|----------|------|------|
| `product-page-client.tsx` | Benefits / package / usage when DB fields empty | Accepted Low |
| `articles-grid-section.tsx` / `latest-articles-section.tsx` | No published articles | Accepted Low |
| `coverage-map.tsx` | No distributor coverage API data | Accepted Low |
| Admin `AdministrationDashboard` API Tokens card | Disabled “Coming soon” tile | Accepted Low (admin only) |

## Hidden admin stubs (not public)

| Page | Gate |
|------|------|
| Pipeline | `canAccess(): false` — D-004 |
| Opportunities | `canAccess(): false` — D-004 |
| Inventory | Route unregistered + mount gate — D-005 |

## Manual checklist (operator — pre-go-live)

Run when staging URL is available:

- [ ] Chrome / Edge / Firefox / Safari — home, products, quote, login, account
- [ ] 375px / 768px / 1280px viewports — nav, forms, tables
- [ ] Submit quote → confirmation email (single mail, not duplicate)
- [ ] Distributor apply → admin notification
- [ ] Password change → security notification

## Related backend evidence

- Notification template parity: `backend/tests/Feature/Notification/NotificationTemplateParityTest.php`
- E2E quote: `backend/tests/Feature/Api/V1/QuoteRequestControllerTest.php`
- Company sync: `backend/tests/Feature/Api/V1/Account/CompanyProfileControllerTest.php`
