# Phase 13.27 — Dropdown Rendering Fix

## Root cause

Native `<select>` elements for Status / Account Manager (create form) and Account Manager / Date Range / Industry / Country / Region / District (filter panel) kept the browser arrow while platform chrome also painted a chevron — same class of defect fixed on Tasks in Phase 13.25.

## Fix

On `.vestra-companies__form-select` and `.vestra-companies__filter-panel-select`:

1. `appearance: none` (+ webkit/moz)
2. Hide `::-ms-expand`
3. Single SVG chevron via `background-image`
4. Extra right padding so values never sit under the icon

No duplicated Heroicons in markup — selects remain plain native controls.
