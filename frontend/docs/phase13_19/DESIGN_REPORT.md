# Phase 13.19 — Design Report

## Visual language

Follows the established CRM workspace shell (Companies / Support / Feedback): hero, KPI grid, filter strip, table card, slide-in detail drawer.

## Inventory-specific UI

| Element | Treatment |
|---|---|
| Product column | 36px thumb + name + category meta |
| SKU | Monospace muted text |
| Warehouse | Name + code meta (no separate Warehouses page) |
| Value | `qty × product.price` formatted as UGX |
| Status | Semantic badge (success / warning / danger) |
| Drawer Adjust | Quantity (signed int) + reason + confirm |

## Explicitly omitted from mockups

- Right-side donut / trend / activity analytics panel
- Incoming quantity column
- Stock Transfer CTA
- Fake “↑ % vs last 30 days” trends

## Motion

- Drawer enter/leave transitions
- Filter / export Alpine dropdowns
- Row hover + primary-color product name hover
