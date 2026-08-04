# Phase 13.15 — Enquiries Workspace: Accessibility Report

## ARIA Roles & Attributes

| Element | Role / Attribute | Value |
|---------|-----------------|-------|
| Enquiry table container | `role="region"` | `aria-label="Enquiries"` |
| Detail drawer outer | `role="dialog"` | `aria-modal="true"`, `aria-label="Enquiry details"` |
| Filter dropdowns | `aria-haspopup="listbox"` | — |
| Export button | `aria-haspopup="true"`, `:aria-expanded` | Dynamic via Alpine |
| Pagination nav | `role="navigation"` | `aria-label="Enquiry pagination"` |
| Active page button | `aria-current="page"` | — |
| Refresh button | `aria-label="Refresh enquiries"` | — |
| Close drawer button | `aria-label="Close details"` | — |
| Sort buttons | `aria-label="Sort by {label}"` | Per column |
| Read icon | `aria-label="Read"` or `"Unread"` | — |
| Replied icon | `aria-label="Replied"` or `"Not replied"` | — |
| Row actions trigger | `aria-label="Enquiry actions"`, `aria-haspopup="true"` | — |
| Reply textarea | `aria-label="Reply draft"` | — |
| Internal notes textarea | `aria-label="Internal notes"` | — |

## Keyboard Navigation

| Key | Behaviour |
|-----|-----------|
| `Tab` | Cycles through all interactive elements in logical order |
| `Enter` / `Space` | Activates focused buttons and checkboxes |
| `Escape` | Closes detail drawer (`@keydown.escape.window`) |
| Arrow keys | Not customised; native browser behaviour in inputs |

## Focus Management

- The drawer overlay captures `@keydown.escape.window` to close on Escape.
- The drawer close button (`×`) is the last item in the sticky header, accessible via Tab after header elements.
- Filter dropdowns close on `@click.outside`, restoring focus to the trigger button.

## Colour Contrast

Badge colours are designed to meet WCAG AA 4.5:1 for normal text:

| Badge modifier | Background | Foreground | Approx. ratio |
|---------------|-----------|-----------|---------------|
| `--primary` | `var(--primary-100)` | `var(--primary-700)` | ≥ 5:1 |
| `--warning` | `#fef9c3` | `#a16207` | ≥ 4.5:1 |
| `--success` | `#dcfce7` | `#15803d` | ≥ 5:1 |
| `--danger` | `#fee2e2` | `#b91c1c` | ≥ 5:1 |
| `--info` | `#dbeafe` | `#1d4ed8` | ≥ 4.8:1 |
| `--gray` | `var(--neutral-100)` | `var(--neutral-600)` | ≥ 4.5:1 |

## Semantic HTML

- Table structure: `<table>`, `<thead>`, `<tbody>`, `<tr>`, `<th scope="col">`, `<td>` — all correct.
- Definition list for drawer contact info: `<dl>`, `<dt>`, `<dd>`.
- Navigation: `<nav>` element wraps pagination.
- Headings: `<h1>` for page title, `<h2>` for drawer title, `<h3>` for section titles — maintains document outline.

## Screen Reader Considerations

- KPI trend values include descriptive `trend_label` strings ("Up 12% vs last month") — rendered as visible text inside the kpi-card component.
- Icon-only cells (Read, Replied) have `aria-label` on the icon element for screen reader announcement.
- Empty state messages are plain `<p>` text, fully readable without visual context.

## Known Limitations

- The assign administrator dropdown within the drawer is not an ARIA `listbox` — it is a custom Alpine dropdown. A future enhancement should add `role="listbox"` and `role="option"` for full ARIA compliance.
- The reply textarea does not trap focus within the drawer — a future focus-trap utility could improve the modal experience.
