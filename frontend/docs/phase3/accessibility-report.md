# Phase 3 — Accessibility Report

## Heading Hierarchy
- Single `h1` from `PageHero`: "About VESTRA®".
- Each section uses `SectionHeader` rendering an `h2`.
- Value cards use `h3` titles.

## ARIA & Semantics
- Each section has `aria-labelledby` pointing to its heading.
- Breadcrumb JSON-LD schema is rendered for navigation context.
- Icon-only elements use appropriate labels inside parent text or existing aria attributes.

## Keyboard Navigation
- All CTAs are real `<Link>` elements with visible focus states.
- Focus-visible ring is defined globally.

## Motion
- Inline motion components respect `prefers-reduced-motion`.
- `MissionVisionCard` and `ValueCard` already use viewport-triggered motion.

## Contrast
- Text on navy backgrounds uses white at 80–100% opacity.
- Green CTAs use white text on `#70c050`.
- Body text uses `--text-body` on white surfaces.
