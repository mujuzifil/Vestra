# Workspace Dashboard Design Report

## Reference

The redesign is based on `Workspace Dashboard.png` at the project root.

## Design Principles

- Clean, premium B2B CRM aesthetic (Salesforce, HubSpot, Stripe Dashboard quality).
- Generous whitespace and clear visual hierarchy.
- Card-based layout with consistent borders, radius, and shadows.
- VESTRA navy/green/gold color palette from Phase 10.
- Dark sidebar with active-item pill highlight.

## Typography

- Page title: `var(--text-h1)`, bold.
- Section/card titles: `var(--text-body)`, semibold.
- Body text: `var(--text-body)` / `var(--text-body-sm)`.
- Muted labels: `var(--text-muted)`.

## Spacing

- Section gaps: `var(--space-6)`.
- Card padding: `var(--space-5)`.
- Grid gaps: `var(--space-6)`.
- Header margin-bottom: `var(--space-6)`.

## Responsive Behavior

- KPI cards stack on mobile, 2-col at `sm`, 3-col at `lg`, 5-col at `xl`.
- Sales Overview + Recent Activity stack on mobile, 2:1 side-by-side at `lg`.
- Tasks/Notifications/Calendar stack on mobile, 2-col at `md`, 3-col at `xl`.
