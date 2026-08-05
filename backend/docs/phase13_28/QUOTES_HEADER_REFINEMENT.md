# Phase 13.28 — Quotes Header Refinement

Removed the Filament `<x-filament-panels::page>` wrapper from `quotes.blade.php` and emptied `QuotesPage::getHeading()` so only the custom `x-quotes.page-header` hero renders:

- **Quotes**
- Manage and track all sales quotes and proposals.

KPI cards, filters, toolbar, table, and empty state were not changed.
