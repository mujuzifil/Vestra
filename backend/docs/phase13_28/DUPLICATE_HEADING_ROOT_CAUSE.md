# Phase 13.28 — Duplicate Heading Root Cause

## Cause

`resources/views/filament/pages/sales/quotes.blade.php` wrapped content in `<x-filament-panels::page>`, which renders Filament's default page heading from `QuotesPage::getTitle()` (`Quotes`).

The same title was also output by `<x-quotes.page-header title="Quotes" …>`.

Result: two `Quotes` headings stacked.

## Not the cause

- CRM shell layout (no page title of its own)
- Shared workspace CSS (CSS hide was not used as the fix)

## Fix at source

1. Drop the Filament page wrapper (same pattern as Tasks / Activity / Companies).
2. Return an empty string from `getHeading()` so any residual Filament heading path stays blank.
