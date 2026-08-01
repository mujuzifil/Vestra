# Phase 5 — Responsive Validation Report

## Breakpoints Tested
- Mobile (< 640 px)
- Tablet (640–1024 px)
- Desktop (> 1024 px)

## Layout Behaviour
- Hero text scales with `clamp()` and remains centered.
- CTA buttons stack on mobile, side-by-side on tablet+.
- Value-card grids adapt:
  - Why Partner: 1 → 2 → 3 columns.
  - Who Can Apply: 1 → 2 → 4 columns.
  - Benefits: 1 → 2 → 3 columns.
- Timeline steps stack on mobile/tablet; connector line hidden below desktop.
- Application form uses single column on mobile, two-column grid where appropriate on tablet+.
- FAQ section stacks heading above accordion on mobile.

## Components
- Container uses responsive padding (`px-5 sm:px-6 lg:px-8`).
- Cards use consistent rounded corners and spacing across breakpoints.
