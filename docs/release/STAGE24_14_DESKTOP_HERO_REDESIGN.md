# Stage 24.14 — Desktop Homepage Hero Redesign

**Date:** 2026-08-14  
**Audit:** [STAGE24_14_DESKTOP_HERO_AUDIT.md](./STAGE24_14_DESKTOP_HERO_AUDIT.md)  
**Implementation:** `frontend/components/sections/hero-section.tsx`  
**Status:** Implemented locally — **not committed, not deployed** (awaiting UAT / approval)

---

## What changed

Desktop (`lg`, 1024px+) is a full-viewport **60/40 split**:

- **Left (~60%):** per-slide kicker, headline, supporting copy, three CTAs in a row, trust statistics.
- **Right (~40%):** campaign artwork as an edge-to-edge scene (`object-cover`), left-edge blend gradient, arrows + indicators.
- **Beneath:** dark “Trusted by” strip (Hotels, Hospitals, Laundry Services, Factories, Schools, Government).

Mobile (`< lg`) keeps the stacked 4:5 slider, original headline, original CTA order, and original chips. Navigation, metadata, and JSON-LD were not modified.

---

## Before / after (desktop)

| | Before | After |
|--|--------|--------|
| Layout | Centered 4:5 poster (~576px) with large navy side gutters | Full-bleed 3fr / 2fr grid, copy aligned to a 1440px content start |
| Copy | Static, below the image | Left column; headline and body change with each slide |
| CTAs | Centered under the image | Horizontal row: Quote, Where to Buy, Distributor (routes unchanged) |
| Motion | Image opacity only | Fade + translate on copy; fade + slight zoom/translate on art (550ms) |
| Controls | Dots only | Dots + previous/next; autoplay pauses on hover/focus |

**Screenshots:** capture during UAT at 1920 / 1600 / 1440 / 1366 / 1280 (desktop) and phone/tablet for mobile regression. Files were not generated in this environment.

---

## Trust statistics (integrity)

Did **not** publish unaudited “500+ businesses”. Desktop stats:

| Display | Basis |
|---------|--------|
| `10+` Industries | 10 sectors in `IndustriesSection`; count-up 650ms when in view |
| Nationwide / Distribution | Existing Why Choose copy |
| Uganda / Manufactured | Existing hero chips |
| Premium / Quality Standards | Existing Why Choose copy |

---

## Performance

| Constraint | Applied |
|------------|---------|
| Extra JS libraries | None (Framer Motion already used) |
| First slide | `priority` retained |
| Desktop `sizes` | `40vw` |
| Particles / lighting | CSS only |
| Lighthouse | **Not re-run here** — compare after preview; expect LCP to improve on wide screens because the hero image occupies ~40% width instead of a small centered poster with empty chrome |

---

## Accessibility

- Stable `h1#hero-heading` (sr-only enterprise title) so `aria-labelledby` works at every breakpoint.
- Carousel region, named previous/next, tablist dots, `aria-live` for slide alt.
- `prefers-reduced-motion` disables autoplay and motion.
- Autoplay pauses on hover/focus; keyboard ArrowLeft / ArrowRight on the carousel region.
- CTA focus rings unchanged (`focus-visible`).

---

## SEO

No edits to `frontend/app/page.tsx` metadata, canonical, or JSON-LD.

---

## Regression checklist (UAT)

| Area | Expected |
|------|----------|
| Navigation | Unchanged labels and order |
| Mobile hero | Stacked 4:5, original copy and CTA order |
| Desktop CTAs | `/request-quote`, `/where-to-buy`, `/distributor` |
| Search / auth | Untouched |
| Tablet portrait | Stacked layout (`< 1024px`) |
| Tablet landscape / laptop | Split layout |

---

## Deployment

Per stage rules: **do not deploy** until internal testing, regression, UAT, and approval. After approval: commit, merge, production deploy, then post-deploy visual check of `/`.
