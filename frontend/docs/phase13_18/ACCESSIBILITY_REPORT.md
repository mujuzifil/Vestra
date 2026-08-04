# Phase 13.18 — Categories: Accessibility Report

## Keyboard & Focus

- Escape closes the detail drawer (`@keydown.escape.window`)
- Sort buttons expose `aria-label="Sort by …"`
- Export / filter menus use `aria-haspopup` / `role="menu"`
- Pagination buttons include previous/next `aria-label`s
- Drawer uses `role="dialog"` + `aria-modal="true"`

## Semantics

- Page sections labeled via `aria-label` (metrics, list)
- Table region is keyboard-focusable (`tabindex="0"`)
- Status conveyed with text labels + icons (not color alone)
- Empty state distinguishes filtered vs truly empty catalogs

## Motion

- Drawer enter/leave uses short CSS transitions
- No auto-playing motion beyond drawer open/close

## Known Follow-ups

- Focus trap inside drawer is not fully implemented (matches sibling workspaces)
- Screen-reader announcement on filter result count relies on visual pagination text
