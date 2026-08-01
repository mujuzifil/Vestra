# Phase 2 — Accessibility Report

## Heading Hierarchy
- Single `h1` in the hero: "Professional Cleaning Solutions Manufactured for Uganda."
- Each section uses `SectionHeader` rendering an `h2`.
- Cards use `h3` for item titles.

## ARIA
- Hero section has `aria-labelledby` pointing to the `h1`.
- All section headings are referenced via `aria-labelledby`.
- Icon-only buttons (search, menu, user, social) include `aria-label`.
- Testimonials use `<article>` semantics.

## Keyboard Navigation
- All CTAs are real `<Link>` elements and are keyboard-focusable.
- Focus-visible styles are provided globally via `globals.css`.
- Mobile menu is toggleable and focusable.

## Motion
- All Framer Motion animations check `prefers-reduced-motion` and disable transforms/opacity changes when the user prefers reduced motion.
- Animations are limited to opacity and translate; no flashing or auto-playing content.

## Contrast
- Text on navy backgrounds uses white at 80–100% opacity.
- Green CTAs use white text on `#70c050` (meets WCAG AA for large/bold text).
- Body text uses `--text-body` (`#475569`) on white surfaces.

## Remaining Work
- Verify colour contrast of footer links with an automated tool once deployed to a public URL.
- Add skip-link targets for each major section if deeper in-page navigation is required.
