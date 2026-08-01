# Phase 10 — Accessibility Audit

## Headings
- Verified every public page renders exactly one `<h1>`.
- Logical hierarchy maintained: `h1` → `h2` (SectionHeader) → `h3` (cards).
- No skipped levels detected in public pages.

## Motion
- `useReducedMotion()` hook added.
- All Framer Motion sections respect reduced motion.
- Global `frontend/styles/motion.css` disables animations for `prefers-reduced-motion: reduce`.

## Focus & keyboard
- All interactive elements use visible `focus-visible:ring-2 ring-secondary-500`.
- Navbar dropdowns support `focus-within`.
- Mobile menu uses `aria-expanded`, `aria-controls`, `aria-hidden`.
- Footer social links have `aria-label`.

## Images
- Hero and product images use `next/image` with `sizes` and `alt` text.
- Review thumbnails and lightbox converted from `<img>` to `next/image`.

## Form accessibility
- Form labels are associated with inputs via `htmlFor`/`id`.
- Error messages use `aria-invalid` and `aria-describedby`.
- `role="alert"` on inline errors.

## Contrast
- Semantic tokens ensure body text meets WCAG 2.2 AA contrast on white surfaces.
- Hero/CTA text uses white with sufficient opacity against dark gradients.

## Remaining notes
- Account and distributor portals share the same token system after this phase; a deeper component-level accessibility review may be scheduled if required.
