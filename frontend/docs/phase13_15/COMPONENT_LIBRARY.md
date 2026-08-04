# Phase 13.15 — Enquiries Workspace: Component Library

All components live in `resources/views/components/enquiries/`.

---

## `x-enquiries.page-header`

**File**: `components/enquiries/page-header.blade.php`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | string | `'Enquiries'` | Page heading |
| `description` | string | `''` | Sub-heading text |
| `csvUrl` | string\|null | `null` | Export CSV href |
| `excelUrl` | string\|null | `null` | Export Excel href |
| `pdfUrl` | string\|null | `null` | Export PDF href |

Contains: search input (`wire:model.live.debounce.300ms="search"`), Refresh button, Export dropdown (Alpine).

---

## `x-enquiries.kpi-cards`

**File**: `components/enquiries/kpi-cards.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `cards` | `array` | Array of KPI card arrays from `EnquiryAdminService::getKpiCards()` |

Renders a 5-column responsive grid using `.vestra-kpi-grid--5`. Delegates each card to `x-admin.kpi-card`.

---

## `x-enquiries.filter-bar`

**File**: `components/enquiries/filter-bar.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `statusOptions` | `ContactStatus[]` | Enum cases |
| `enquiryTypeOptions` | `ContactEnquiryType[]` | Enum cases |
| `priorityOptions` | `Priority[]` | Enum cases |
| `sourceOptions` | `string[]` | Distinct DB source values |
| `assigneeOptions` | `array[]` | `[id, name]` admin user list |

Each filter is an Alpine dropdown. Source and Assigned To are conditionally rendered when non-empty. Date range uses native `<input type="date">`.

---

## `x-enquiries.enquiry-table`

**File**: `components/enquiries/enquiry-table.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `enquiries` | `LengthAwarePaginator` | Page of enquiry models |
| `sortField` | string | Active sort column |
| `sortDirection` | `asc\|desc` | Active sort direction |

Renders 10 columns with sortable header buttons (`wire:click="sortBy(...)"`) for: Sender, Type, Priority, Status, Assigned To, Received.

---

## `x-enquiries.enquiry-row`

**File**: `components/enquiries/enquiry-row.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `enquiry` | `ContactMessage` | Model instance |
| `sortField` | string | Passed through for potential row highlighting |

Highlights unread rows with `.vestra-enquiries__row--unread`. Clicking sender name calls `$wire.openDetailDrawer(id)`.

---

## `x-enquiries.status-badge`

**File**: `components/enquiries/status-badge.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `status` | `ContactStatus\|string\|null` | Status value or enum |

Resolves label and color from `ContactStatus::tryFrom()`. Renders `.vestra-enquiries__badge--{color}`.

---

## `x-enquiries.priority-badge`

**File**: `components/enquiries/priority-badge.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `priority` | `string\|null` | Priority string value |

Resolves label and color from `Priority::tryFrom()`. Renders `—` when null.

---

## `x-enquiries.empty-state`

**File**: `components/enquiries/empty-state.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `hasFilters` | bool | `false` = "no enquiries yet"; `true` = "no match" + clear button |

---

## `x-enquiries.pagination`

**File**: `components/enquiries/pagination.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `paginator` | `LengthAwarePaginator` | Paginator from Livewire computed property |

Standard Vestra pagination: info text left, prev/page/next controls right.

---

## `x-enquiries.detail-drawer`

**File**: `components/enquiries/detail-drawer.blade.php`

| Prop | Type | Description |
|------|------|-------------|
| `show` | bool | Controls visibility via Alpine `open` data |
| `enquiry` | `array\|null` | Flat array from `EnquiryAdminService::getDetail()` |

Sections within drawer:
1. Header (avatar, name, email/company)
2. Badges (status, priority, type)
3. Quick actions (Mark Resolved, Print)
4. Contact Information (phone, source, timestamps)
5. Subject & Message
6. Attachments
7. Reply (textarea + Save Draft / Send Reply)
8. Assigned Administrator + Reassign dropdown
9. Update Status buttons
10. Internal Notes textarea + Save Notes
