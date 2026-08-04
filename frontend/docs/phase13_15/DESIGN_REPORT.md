# Phase 13.15 — Enquiries Workspace: Design Report

## Design System Alignment

The Enquiries workspace uses the Vestra design token system exclusively — no inline styles or hardcoded values.

### Token Usage

| Token category | Usage |
|---------------|-------|
| `--primary-*` | KPI card accent, unread row tint, badge colors, avatar backgrounds |
| `--surface-card` | Table card, drawer panel, filter dropdowns |
| `--border-default / --border-subtle` | Table rows, drawer sections, filter triggers |
| `--text-heading / --text-body / --text-muted` | Hierarchy across all text elements |
| `--space-*` | All spacing — padding, gap, margin |
| `--radius-*` | Badges (full), dropdowns (lg), inputs (md) |
| `--shadow-*` | Drawer (xl), dropdowns/menus (lg), focus ring |
| `--duration-fast / --ease-base` | Transition timing on interactive elements |

## Visual Hierarchy

1. **Page header** — large title, inline search, export CTA
2. **KPI cards** — 5-column grid, icon + value + trend
3. **Filter bar** — compact triggers, resets far right
4. **Table** — zebra-like unread highlight; sticky header via scroll container
5. **Drawer** — right slide-in, 560px max-width, overlay backdrop, sticky header

## Responsive Breakpoints

| Breakpoint | Layout change |
|-----------|--------------|
| `< 768px` | KPI grid: 2 columns; filter dropdowns collapse; table scrolls horizontally |
| `768px` | KPI grid: 3 columns |
| `1280px` | KPI grid: 5 columns (full row) |

## Badge Color System

Badges use semantic CSS modifier classes:

```
.vestra-enquiries__badge--primary   → ContactStatus::NEW
.vestra-enquiries__badge--warning   → ContactStatus::IN_PROGRESS / Priority::HIGH
.vestra-enquiries__badge--success   → ContactStatus::RESOLVED / Priority::MEDIUM (partially)
.vestra-enquiries__badge--danger    → Priority::CRITICAL / Unassigned KPI
.vestra-enquiries__badge--info      → Priority::LOW / KPI New
.vestra-enquiries__badge--gray      → Priority::NEUTRAL / unknown
```

## Unread State

Rows where `read_at IS NULL` receive the `.vestra-enquiries__row--unread` modifier:
- Background: `var(--primary-50)` (light blue)
- Hover: `var(--primary-100)` (slightly deeper)

This provides a clear visual distinction for new, unread enquiries without being distracting.

## Drawer Transitions

The detail drawer uses Alpine.js `x-transition` directives:
- **Enter**: `opacity-0 + translate-x-4` → `opacity-100 + translate-x-0` over 200ms ease-out
- **Leave**: `opacity-100 + translate-x-0` → `opacity-0 + translate-x-4` over 150ms ease-in

The overlay backdrop uses a 35% black scrim for modal focus effect.

## CSS File

`resources/css/filament/admin/components/enquiries.css` — ~500 lines, BEM-flavored with `vestra-enquiries__` namespace. Imported at the end of `theme.css`.

## Accessibility

See `ACCESSIBILITY_REPORT.md` for full details. Key points:
- All interactive elements have `aria-label` attributes
- Drawer has `role="dialog"` and `aria-modal="true"`
- Keyboard: Escape closes drawer
- Focus management: close button in drawer header is the first focusable element after open
