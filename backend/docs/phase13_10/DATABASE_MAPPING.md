# Phase 13.10 — Applications Workspace: Database Mapping

## Source table

`distributor_requests` (model: `App\Models\DistributorRequest`)

Built from `2026_07_15_140004_create_distributor_requests_table.php` and extended by
`2026_07_21_060000_extend_distributor_requests_table.php`.

| Column | Type | Used by |
|---|---|---|
| `id` | bigint PK | Row identity, detail drawer, approve/reject/bulk actions |
| `company_name` | string | Table column, search, KPI trend context, export |
| `business_type` | string, nullable | Detail drawer, export |
| `years_in_operation` | integer, nullable | Detail drawer |
| `contact_person` | string | Table column, search, detail drawer, export |
| `email` | string | Table column, search, detail drawer, export |
| `phone` | string, nullable | Search, detail drawer, export |
| `address` | string, nullable | Search, `formattedAddress()`, export |
| `country` | string, nullable | Filter (`countryFilter`), table column, export |
| `region` | string, nullable | Filter (`regionFilter`), table column, export |
| `business_description` | text, nullable | Search, detail drawer |
| `products_interested_in` | text, nullable | Detail drawer |
| `target_region` | string, nullable | Detail drawer |
| `estimated_volume` | string, nullable | Detail drawer, export |
| `existing_customer` | boolean | Detail drawer (`isExistingCustomer()`), export |
| `previous_applications` | integer | Detail drawer |
| `status` | string (cast to `DistributorStatus` enum) | Filter (`statusFilter`), KPI cards, badges, sort, approve/reject guards |
| `priority` | string (cast via `Priority` enum accessors) | Filter (`priorityFilter`), badges, sort |
| `assigned_to` | bigint FK → `users.id`, nullable | Filter (`assignedToFilter`), `assignedAdministrator` relation, sort |
| `internal_notes` | text, nullable | Detail drawer |
| `documents` | json, nullable (cast `array`) | Detail drawer |
| `created_at` / `updated_at` | timestamps | Sort, KPI month-over-month comparison, date range filter |

## Relationships

- `DistributorRequest::assignedAdministrator()` → `belongsTo(User::class, 'assigned_to')`
  — eager-loaded in `ApplicationAdminService::queryApplications()` via `->with(['assignedAdministrator'])`.
- Detail drawer additionally looks up `Distributor::where('distributor_request_id', $application->id)`
  to show the resulting distributor account once an application has been approved. This is a
  read-only lookup, not a defined Eloquent relation, mirroring how `DistributorOnboardingService`
  creates the `Distributor` row on approval.

## Enums used (values used verbatim, no re-mapping)

`App\Enums\DistributorStatus`: `pending`, `under_review`, `information_requested`, `approved`, `rejected`
`App\Enums\Priority`: used via `Priority::cases()` for filter options and `tryFrom()` for labels/colors.

## Model scopes added for this phase

- `scopeSearch(string $term)` — case-insensitive `LIKE` across company/contact/email/phone/address/description
- `scopeStatusIn(array $statuses)` — `whereIn('status', ...)`, ignores empty filter
- `scopePriorityIn(array $priorities)` — `whereIn('priority', ...)`, ignores empty filter
- Pre-existing scopes reused as-is: `scopePending`, `scopeUnderReview`, `scopeInformationRequested`,
  `scopeApproved`, `scopeRejected` (used for live KPI counts)

## No schema changes

This phase is presentation-only. No migrations were added or modified for the `distributor_requests`
table; all data displayed comes directly from existing columns/relations with no denormalization or
duplication.
