# Stage 24.14 — Desktop Homepage Hero Audit

**Date:** 2026-08-14  
**Scope:** Presentation of `frontend/components/sections/hero-section.tsx` on desktop viewports  
**Status:** AUDIT COMPLETE — safe to implement behind `lg:` without changing navigation, SEO, or mobile markup  
**Deploy:** Not in this stage until UAT approval (per stage rules)

---

## 1. Current homepage implementation

| Item | Evidence |
|------|----------|
| Page | `frontend/app/page.tsx` composes `HeroSection` then Why Choose, Categories, Industries, Featured Products, Manufacturing, Distributor CTA, Request Quote, Testimonials, Articles, Contact Banner |
| Hero owner | Single client component `HeroSection` (`frontend/components/sections/hero-section.tsx`) |
| Nav | `frontend/components/navigation/navbar.tsx` — Home, About Us, Products, Become a Distributor, Request a Quote, Where to Buy, Blog, Contact. **Out of scope.** |
| SEO | `createMetadata` + `JsonLd` (`organizationSchema`, `websiteSchema`, `manufacturerSchema`) on the homepage. **Must not change.** |

The hero is stacked: portrait campaign slides on top, then centered copy, CTAs, and three feature chips. There is no split layout and no trust strip.

---

## 2. Hero slider architecture

- Four WebP slides (WhiteMax, Silk Care, Eco Suit, range) at native ~864×1080 (4:5).
- Shared `activeIndex` + `setInterval` (5s). Disabled when `prefers-reduced-motion: reduce`.
- Crossfade only (`opacity`, 0.9s) via Framer Motion. Copy does **not** change per slide.
- Controls: dot `tablist` only. No arrows, no pause-on-hover.
- First image: `priority`. Others in the same stack (opacity 0) — acceptable for four local assets.

---

## 3. CTA components

Existing `Button` + `Link` (Stage 24.13B), routes unchanged:

| Label | href | `data-track` |
|-------|------|----------------|
| Request a Quote | `/request-quote` | `hero-primary-cta` |
| Become a Distributor | `/distributor` | `hero-secondary-cta` |
| Where to Buy | `/where-to-buy` | `hero-where-to-buy-cta` |

Mobile order today: Quote → Distributor → Where to Buy. Desktop spec order: Quote → Where to Buy → Distributor. **Reorder on desktop only.**

---

## 4. Animations

- `useReducedMotion` (`frontend/hooks/use-reduced-motion.ts`).
- Hero copy: one-shot fade/translate on mount.
- No viewport count-up, no per-slide copy animation, no Ken Burns.

Reuse Framer Motion + reduced-motion gate. Do not add a new animation library.

---

## 5. Image rendering

- `next/image` + `fill` + `object-contain` inside `aspect-[4/5]` capped at `max-w-[36rem]`.
- **Desktop defect:** on 1366–1920px the poster is a ~576px column, leaving large navy gutters.
- Desktop fix: right-hand ~40% column, `object-cover`, edge-to-edge to the viewport right. Column aspect is close to 4:5, so crop is minimal compared with a short landscape strip.

Mobile keeps `object-contain` in the 4:5 frame.

---

## 6. Responsive breakpoints

Tailwind v4 defaults: `sm` 640, `md` 768, `lg` 1024, `xl` 1280, `2xl` 1536.

| Band | Plan |
|------|------|
| &lt; 1024px | **Unchanged** current stacked hero (`lg:hidden`) |
| ≥ 1024px | New 60/40 split (`hidden lg:grid`) |
| Tablet portrait | Stays on stacked layout |
| Tablet landscape / laptop 1280+ | Split layout |

Global `Container` is `max-w-[1320px]`. Desktop hero will use a local `max-w-[1440px]` / full-bleed image column and **must not** change `Container`.

Navbar height used in hero padding: `72px` + safe-area.

---

## 7. Desktop width usage (before)

| Viewport | Approximate used by image | Unused |
|----------|---------------------------|--------|
| 1920px | ~576px centered | ~1344px gutters |
| 1440px | ~576px | ~864px |
| 1280px | ~576px | ~704px |
| 390px | full width 4:5 | none (acceptable) |

---

## 8. Lighthouse / performance baseline

No Stage 24.14 CI Lighthouse capture in this environment. Constraints for the redesign:

- Keep first slide `priority`; do not `priority` all four.
- Desktop `sizes` ≈ `40vw`; mobile stays ~`100vw`.
- CSS gradients/particles only (no canvas, no extra deps).
- Autoplay remains one `setInterval`.
- Do not change homepage metadata or JSON-LD (no SEO regression).

---

## 9. SEO

Homepage title, description, canonical (`https://vestradetergents.com`), Open Graph, and JSON-LD live in `page.tsx` / `lib/metadata.ts` / `lib/structured-data.tsx`. **No edits.** Visible desktop headlines may vary per slide; `id="hero-heading"` remains on the desktop/mobile H1 for the active (or default) message. `aria-live` already announces the slide alt.

---

## 10. Accessibility

Already present: `aria-labelledby`, slide `aria-hidden`, dot `tablist`/`tab`, `aria-live="polite"`, reduced motion, skip-to-content in root layout, button `focus-visible` rings.

Gaps to close on desktop only: previous/next buttons with names, pause autoplay on hover/focus, keyboard left/right on the slider region, sufficient contrast on overlay text, do not invent unverifiable numeric claims as facts.

---

## 11. Reuse vs redesign vs do not touch

**Reuse**

- `Button`, `Link`, `Icon`, `useReducedMotion`, Framer `motion`, existing WebP assets, CTA hrefs and `data-track` values, navbar, `page.tsx` section order except an optional desktop trust strip **inside** the hero module.

**Redesign (desktop only)**

- Layout: 60% copy / 40% scene, ~full viewport height.
- Per-slide headline, body, background tint, CTA emphasis.
- Trust statistics (only claims already supported on-site).
- Trust strip under the hero (desktop).
- Arrows + indicators.

**Refactor, do not duplicate CSS globally**

- Keep styles in the hero module via Tailwind. Do not fork `Button` variants. Do not change `globals.css` unless a tiny hero-scoped utility is required (prefer not to).

**Shared / must remain untouched**

- `Navbar` items, labels, order.
- Homepage metadata and JSON-LD.
- Mobile hero JSX (copy, CTA order, 4:5 contain slider).
- Downstream homepage sections.

---

## 12. Trust statistics — integrity lock

The brief’s “500+ Businesses / 20+ Industries” is **example copy**. There is no audited customer count in the codebase. Fabricating it would be a content defect.

Supported by existing site copy / components:

| Stat | Source |
|------|--------|
| 10 industries | `IndustriesSection` lists 10 sectors |
| Nationwide distribution | `WhyChooseSection` |
| Manufactured in Uganda | current hero chips |
| Premium quality standards | `WhyChooseSection` |

Animate only the real `10` count. Other cells are labels, not fake counters.

Trust strip sectors: Hotels, Hospitals, Laundry Services, Factories, Schools, Government — all present in `IndustriesSection` (laundry mapped from “Commercial Laundries”).

---

## 13. Safest implementation path

1. Keep **one** `HeroSection` so autoplay state is shared.
2. Duplicate layout into two trees: `lg:hidden` (current) and `hidden lg:grid` (new). Accept duplicated CTA markup to guarantee mobile pixels do not drift.
3. Add desktop trust strip as the last child of the same `<section>` (or immediately after the split, still in this component) with `hidden lg:flex` so mobile does not gain a new band.
4. Do not modify `navbar.tsx`, `page.tsx` metadata, or `Container`.
5. Do not deploy until UAT approval.

**Gate:** READY TO IMPLEMENT.
