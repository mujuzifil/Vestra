# Phase 13.27 — Filter Panel Fix

## Problem

Filter panel opened on every page load (`showFilterPanel = true`), so the Filters control looked active immediately and “Clear all / Apply Filters” were always visible.

## Fix

- Default `showFilterPanel = false`
- Stop forcing the panel open in `applyFilters()`
- Button active state: `$showFilterPanel || $activeFilterCount > 0`
- Closing the panel with no applied filters restores the inactive appearance; applied filters keep the badge/active affordance

## Tests

- `test_filter_panel_is_closed_by_default`
- `test_filter_panel_opens_only_after_toggle`
- Polished columns test asserts panel content appears only after toggle
