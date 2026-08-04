# Phase 13.15 — Enquiries Workspace: API Reference

## EnquiriesPage (Livewire Component)

**Class**: `App\Filament\Pages\CustomerSuccess\EnquiriesPage`  
**Route slug**: `customer-success/enquiries`  
**Named route**: `filament.admin.pages.customer-success.enquiries`

### URL State Properties

| Property | URL param | Type | Default |
|----------|-----------|------|---------|
| `$search` | `search` | `string` | `''` |
| `$statusFilter` | `status` | `string[]` | `[]` |
| `$sourceFilter` | `source` | `string[]` | `[]` |
| `$enquiryTypeFilter` | `enquiry_type` | `string[]` | `[]` |
| `$priorityFilter` | `priority` | `string[]` | `[]` |
| `$assignedToFilter` | `assigned_to` | `int\|null` | `null` |
| `$dateFrom` | `date_from` | `string\|null` | `null` |
| `$dateUntil` | `date_until` | `string\|null` | `null` |
| `$sortField` | `sort` | `string` | `created_at` |
| `$sortDirection` | `direction` | `string` | `desc` |

### Computed Properties

| Property | Returns |
|----------|---------|
| `$this->enquiries` | `LengthAwarePaginator<ContactMessage>` |
| `$this->kpiCards` | `array<int, array<string, mixed>>` |
| `$this->selectedEnquiry` | `array<string, mixed>\|null` |
| `$this->filterOptions` | `array<string, mixed>` |

### Livewire Methods

| Method | Parameters | Gate | Description |
|--------|-----------|------|-------------|
| `openDetailDrawer` | `int $id` | `view` | Open drawer, mark read |
| `closeDetailDrawer` | — | — | Close drawer, reset draft |
| `saveReply` | — | `update` | Persist reply draft |
| `sendReply` | — | `update` | Send mail via ContactReplyMail |
| `assign` | `int $id, int $userId` | `update` | Set assigned_to |
| `updateStatus` | `int $id, string $status` | `update` | Change status |
| `markResolved` | `int $id` | `update` | Set status = resolved |
| `saveInternalNotes` | `int $id, string $notes` | `update` | Persist internal_notes |
| `sortBy` | `string $field` | — | Toggle sort |
| `resetFilters` | — | — | Clear all filters |
| `getExportUrl` | `string $format` | — | Build export route URL |

---

## EnquiryAdminService

**Class**: `App\Services\Admin\EnquiryAdminService`

### Methods

#### `paginateEnquiries(array $filters, string $sort, string $direction, int $perPage): LengthAwarePaginator`

Applies all active filters and returns a paginator with the `assignedTo` relation eager-loaded.

#### `queryEnquiries(array $filters, string $sort, string $direction): Builder`

Returns an Eloquent Builder with filters and sorting applied. Used by both `paginateEnquiries` and `exportRows`.

#### `getKpiCards(): array`

Returns 5 KPI cards comparing current totals to pre-current-month baseline:
- Total, New, In Progress, Resolved, Unassigned

#### `getDetail(ContactMessage $enquiry): array`

Returns a flat associative array suitable for Blade rendering. Includes `assignee` sub-array with `initials()`.

#### `getFilterOptions(): array`

```php
[
    'statuses'      => ContactStatus::cases(),
    'enquiry_types' => ContactEnquiryType::cases(),
    'priorities'    => Priority::cases(),
    'sources'       => string[],   // distinct non-empty values from DB
    'assignees'     => ['id' => int, 'name' => string][],
]
```

#### `exportRows(array $filters): array`

Returns flat rows with columns: `name, company, email, phone, subject, enquiry_type, status, priority, source, assigned_to, replied_at, created_at`.

---

## EnquiryExportController

**Route**: `GET customer-success/enquiries/export`  
**Named**: `filament.admin.customer-success.enquiries.export`  
**Gate**: `export` on `ContactMessage::class`

### Query Parameters

| Param | Type | Description |
|-------|------|-------------|
| `format` | `csv\|excel\|pdf` | Export format (default: csv) |
| `search` | `string` | Full-text search |
| `status[]` | `string[]` | Status filter values |
| `source[]` | `string[]` | Source filter values |
| `enquiry_type[]` | `string[]` | Enquiry type filter values |
| `priority[]` | `string[]` | Priority filter values |
| `assigned_to` | `int` | Assignee user ID |
| `date_from` | `Y-m-d` | Lower bound for created_at |
| `date_until` | `Y-m-d` | Upper bound for created_at |

### Response Headers

| Format | Content-Type |
|--------|-------------|
| csv | `text/csv; charset=UTF-8` |
| excel | `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` |
| pdf | `application/pdf` |

---

## ContactMessagePolicy — added method

```php
public function export(User $user): bool
{
    return $user->isAdmin();
}
```

Gate call: `Gate::authorize('export', ContactMessage::class)`
