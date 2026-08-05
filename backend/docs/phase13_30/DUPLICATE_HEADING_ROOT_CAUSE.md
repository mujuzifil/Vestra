# Duplicate Heading Root Cause

## Symptom

Workspace pages rendered the page name twice, e.g.:

```
Active Partners
Active Partners
```

## Cause

Two independent title renderers were active:

1. **Filament** — `<x-filament-panels::page>` renders the Filament page heading from `getTitle()` / `getHeading()`.
2. **Workspace Design System** — each page’s Blade view includes a custom `x-*.page-header` with an `h1` title and subtitle.

Both ran on the same page, producing a duplicate heading.

## Incorrect Fix (rejected)

Hiding Filament’s heading with CSS. That leaves duplicate markup and can break accessibility / layout spacing.

## Correct Fix

1. Remove `<x-filament-panels::page>` … `</x-filament-panels::page>` from each Workspace Blade view.
2. Add `getHeading()` returning an empty string so Filament does not inject a heading even if layout wiring changes.

`getTitle()` remains for browser tab / navigation labelling.
