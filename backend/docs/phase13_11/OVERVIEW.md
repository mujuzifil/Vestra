# Phase 13.11 — Active Partners Workspace

## Summary

Introduces a dedicated CRM workspace for managing Distributor accounts ("Active Partners"), mirroring the Quotes/Companies workspace pattern established in Phase 13.6/13.7. The workspace replaces the generic `DistributorResource` list page as the primary way admins browse, filter, and inspect distributor accounts.

## What changed

- **New page**: `App\Filament\Pages\Distributors\ActivePartnersPage` — Livewire CRM page under the `Distributors` navigation group, slug `distributors/active-partners`.
- **New service**: `App\Services\Admin\PartnerAdminService` — encapsulates querying, pagination, KPI aggregation, detail assembly, and CSV/Excel/PDF export for `Distributor` records.
- **New export controller**: `App\Http\Controllers\Admin\PartnerExportController`, registered as `distributors/active-partners/export`.
- **Policy update**: `App\Policies\DistributorPolicy` gained `viewAny()` (admin only) and `export()` (admin only) abilities.
- **Navigation cleanup**: `DistributorResource` navigation is hidden (`$shouldRegisterNavigation = false`); its `ListDistributors` page now redirects to `ActivePartnersPage` for any direct links, matching the pattern already used for `CustomerResource` → `CompaniesPage`.
- **Model scopes**: `Distributor` gained `scopeSearch()`, `scopeStatusIn()`, and `scopeInRegions()` query scopes used by the new service.

## Why

Distributors previously only had a basic Filament resource table (search, status filter, view action). Sales/Ops teams need the same enterprise CRM experience (KPI cards, rich filters, detail drawer, exports) already delivered for Companies and Quotes. This phase extends that pattern to the Distributors domain without introducing any fabricated/mock data — every KPI and detail field is sourced from live relations (`user`, `salesRepresentative`, `creditAccount`, `branches`, `documents`, `serviceAreas`, and `Order`).

## Out of scope

- Territory management (`distributor_service_areas` browsing/editing) — tracked separately as Phase 13.12.
- Credit account administration UI — tracked separately as Phase 13.13.
- Partner performance analytics/reporting page — no such route exists yet, so no link/button was added for it per the phase brief.
- Create/Edit forms for distributors — the workspace is read-oriented (search, filter, view, export), consistent with how distributor records are provisioned via the approval workflow (`DistributorRequest`), not created ad-hoc from this screen.
