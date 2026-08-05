# Applications Header Fix

## Problem

The Applications workspace rendered the page title twice:

```
Applications
Applications
```

## Root Cause

The Blade view wrapped custom workspace content in `<x-filament-panels::page>`, which renders Filament’s default page heading from `getTitle()`. The custom `x-applications.page-header` also rendered an `h1`, producing a duplicate.

## Fix

1. Removed the `<x-filament-panels::page>` wrapper from `resources/views/filament/pages/distributors/applications.blade.php` (same pattern as Quotes / Companies).
2. Added `getHeading(): string|\Illuminate\Contracts\Support\Htmlable` returning `''` on `ApplicationsPage` so Filament does not inject a heading.

## Result

The page shows a single title and the subtitle:

```
Applications
Review and manage incoming distributor applications.
```
