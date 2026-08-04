# Phase 13.11 — Responsive Report

## Breakpoints

Follows the same breakpoints as the Quotes/Companies workspaces (`max-width: 767px` and `max-width: 639px`).

## Behaviour

| Viewport | Behaviour |
|----------|-----------|
| Desktop (≥1024px) | 5-column KPI grid; filter bar inline; table scrolls horizontally beyond `min-width: 960px` |
| Tablet (640–1023px) | KPI grid collapses to 2 columns; filter bar wraps |
| Mobile (<640px) | Hero stacks vertically; header actions (Refresh/Export) become full-width stacked buttons; filter triggers become full-width; detail drawer panel expands to full viewport width |

## Table overflow

The partner table (`.vestra-partners__table-wrap`) scrolls horizontally within its card on narrow viewports rather than clipping columns, consistent with `.vestra-quotes__table-wrap`.

## Detail drawer

`.vestra-partners-detail__panel` is capped at `max-width: 520px` on desktop and expands to `max-width: none` (full width) under 640px, matching the Quotes detail drawer behaviour.

## Reduced motion

All interactive transitions (row hover, filter triggers, action menu, pagination buttons, drawer close) are disabled under `@media (prefers-reduced-motion: reduce)`.
