# Design Report — Phase 13.5

## Overview

The Activity Centre UI follows the same custom CRM design system used by the Workspace Dashboard, Tasks Workspace, and Notifications Workspace. It extends the existing component library without introducing a new visual language.

## Layout

1. **Hero section** — page title, subtitle, Refresh action, and Export dropdown.
2. **Five KPI cards** — Total Activities, User Activities, Security Events, Module Activities, System Events.
3. **Filter bar** — search input, category/status/module multi-selects, user single-select, date range, and reset.
4. **Bulk action bar** — appears when rows are selected (currently supports clearing selection).
5. **Activity feed** — timeline of activity cards with desktop column headers.
6. **Pagination** — custom controls showing result range and page numbers.
7. **Detail drawer** — slides in from the right with full metadata and related-record link.

## Visual Hierarchy

- Page title and primary actions sit at the top.
- KPI metrics give immediate quantitative context.
- Filters control the dataset without dominating the page.
- The timeline is the primary content area, with a vertical connector line for scannability.
- Status and category badges use the established colour palette.
- Actor avatars and initials match the notification card pattern.

## Reference Image

The implementation uses `Activity.png` at the project root for visual intent: KPI cards, filter bar, timeline with avatars, module badges, and a detail panel. The final UI does not pixel-copy the mock but adapts those patterns to the existing VESTRA Workspace design language.
