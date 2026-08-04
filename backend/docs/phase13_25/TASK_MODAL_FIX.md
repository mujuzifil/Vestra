# Phase 13.25 — Create Task Modal Dropdown Fix

## Problem

Status, Priority, and Assignee native `<select>` fields in the Create/Edit Task drawer showed overlapping dropdown arrows. Selected values collided with the chevron.

## Root cause

`.vestra-tasks__form-select` kept the browser’s native select arrow while the admin theme / platform chrome also painted a chevron affordance. Native `appearance` was left at the default, so two indicators stacked.

There were no duplicated Heroicon SVGs in the Blade markup — the selects are plain `<select>` elements without wrapper icons. The overlap came from CSS + native UI, not Alpine or Filament Select components.

## Fix

In `resources/css/filament/admin/components/tasks.css`:

1. `appearance: none` (+ `-webkit-` / `-moz-`) on `.vestra-tasks__form-select`
2. Hide IE/Edge legacy expander: `::-ms-expand { display: none }`
3. Single SVG chevron via `background-image` (Heroicons-style path), positioned `right 0.75rem center`
4. Extra right padding (`2.5rem`) so the value never sits under the chevron

This matches the Workspace filter-dropdown visual language (one indicator, aligned value).

## Validation

- One chevron per select
- Keyboard / tab order unchanged (native select)
- No markup change required for Status / Priority / Assignee
