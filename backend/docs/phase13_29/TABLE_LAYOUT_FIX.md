# Table Layout Fix

## Problem

The applications table action (⋮) menu was clipped by parent containers with `overflow: hidden` / scroll containment.

## Fix

1. `.vestra-applications__table-card` — `overflow: hidden` → `overflow: visible`
2. `.vestra-applications__table-wrap` — keep horizontal scroll; set `overflow-y: visible`
3. Action menu positioning — Alpine computes `position: fixed` from the trigger’s `getBoundingClientRect()` so the menu floats above surrounding content
4. `.vestra-applications__action-menu` — CSS updated for fixed stacking (`z-index: 80`)
5. Actions cell — `overflow: visible` with elevated stacking context

## Result

Opening the three-dot menu shows the full action list (View Details / Approve / Reject) without clipping behind cards or the table container.
