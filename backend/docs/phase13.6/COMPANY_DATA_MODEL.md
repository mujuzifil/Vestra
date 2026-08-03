# Phase 13.6 — Company Data Model

## Core Entity

`App\Models\CompanyProfile` is the single source of truth for a company record.

### Existing Fields

- `user_id` — primary portal contact (unique, foreign key to `users`)
- `company_name`, `industry`, `business_type`
- `tax_identification`, `registration_number`, `website`
- `district`, `city`, `country`, `address`
- `primary_contact_name`, `primary_contact_phone`, `primary_contact_email`
- `timestamps`

### Added Fields (migration `2026_08_03_100000_add_status_and_fields_to_company_profiles_table`)

| Column | Type | Default / Notes |
|--------|------|-----------------|
| `status` | string | default `prospect` |
| `account_manager_id` | nullable FK | `users.id`, `nullOnDelete` |
| `region` | nullable string | |
| `notes` | nullable text | |

### Cast

- `status` → `App\Enums\CompanyStatus::class`

### Relations

- `user()` — belongs to the primary portal contact
- `accountManager()` — belongs to a staff user
- `quoteRequests()` — `hasMany` through `user_id`
- `supportTickets()` — `hasMany` through `user_id`
- `customerDocuments()` — `hasMany` through `user_id`

### Scopes

- `statusIn(array $statuses)`
- `withOpenQuotes()`
- `withActiveTickets()`
- `createdThisMonth()`
- `search(string $term)` — searches company name, contact details, tax ID and registration number
