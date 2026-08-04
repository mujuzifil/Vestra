# Phase 13.26 — Activity Layout Refinement

## Heading

Removed duplicate Filament page heading by dropping `<x-filament-panels::page>` (Tasks pattern) and emptying `getHeading()`. CRM CSS also hides `.fi-header` / `.fi-page-header` inside `.vestra-crm` for remaining workspace pages that still wrap Filament’s page component.

## KPI overflow

- Activity 5-column KPI grid now uses `minmax(0, 1fr)` and only reaches 5 columns at `1280px` (aligned with shell KPI grid).
- Content shell / workspace containers set `min-width: 0` and `overflow-x: hidden`.
- KPI cards omit the “No comparison available” trend row when trends are unavailable.

## Timeline density

- Compact row layout: smaller icon (32px), tighter padding, single border separators.
- Timeline rail removed from the card chrome.
- **View** button and detail drawer removed (page no longer opens a modal).
- Status + category badges sit inline with the title.
- Default page size increased to **30** activities.

## Notifications removal

Deleted Workspace Notifications page, Blade views, components, CSS, and `NotificationsPageTest`. Domain notification APIs remain unchanged.
