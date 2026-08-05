# Table Optimisation — Phase 13.31

## Problems

- Too many columns forced horizontal scrolling.
- Parent `.vestra-enquiries__table-card { overflow: hidden }` clipped the ⋮ action menu.

## Fixes

| Change | Detail |
|--------|--------|
| Columns removed | Assigned To, Read, Replied |
| Remaining | Sender, Subject, Type, Priority, Status, Received, Actions |
| Width | Subject/sender max-widths; table uses available width |
| Menu | Alpine fixed positioning from trigger `getBoundingClientRect()` |
| Card | `overflow: visible` |

Menus now float above surrounding content without clipping.
