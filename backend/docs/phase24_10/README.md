# Stage 24.10 — Companies & Quotes CRM + UI Hotfixes

## Objective

Harden Companies and Quotes as one live CRM workflow rooted in account company profiles, public quote requests, and approved distributor applications. Ship the Activity/Applications/Support/Products/Blog UI hotfixes.

## Architecture

- `CompanyProfile` remains the canonical CRM company
- `QuoteRequest.company_profile_id` links every quote to a company (backfilled on migrate)
- Guest public quotes create/find a user by email, then resolve/create company profile
- Distributor approval syncs/creates company via `CompanyProfileService::syncFromDistributorApproval`
- Quote status changes emit `QuoteRequestStatusChanged` for notifications + activity

## UI hotfixes

1. Activity feed converted to standard table + footer pagination card
2. Applications approve refreshes status cell (`wire:key`), surfaces validation errors, keeps drawer open, creates company
3. Support KPI grid `--6` full-width rules
4. Product currencies curated (East Africa + majors); affix select chevron without spinner clash
5. Blog article toolbar restores selection and refreshes active state after format

## Companies CRM

- Search across name, registration, contacts, email, phone, country/region/city/district
- Detail drawer: quotes, tickets, feedback, documents, activity, deactivate, Create Quote (company-filtered Quotes URL)
- Admin create uses shared user+profile path

## Quotes CRM

- Public submit always attempts company link
- Admin status workflow uses existing enum; Approve/Reject fire status-changed notifications
- Account `/account/quotes` continues to read the same `QuoteRequest` rows

## RBAC / notifications

- Permission discovery maps `QuotesPage` → `QuoteRequest`
- Templates: `quote_request.approved`, `quote_request.declined`, `quote_request.status_changed`

## Validation

```bash
cd backend
php artisan test --filter='ApplicationsPageTest|QuotesPageTest|CompaniesPageTest|QuoteRequestControllerTest|ProductAdmin'
```

## Deploy

Merge feature → `develop` → `master`, then:

```bash
./scripts/deploy.sh --build
```
