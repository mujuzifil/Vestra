# Phase 13.15 — Enquiries Workspace: Data Mapping

## ContactMessage Model Fields

| Field | Type | Cast | Notes |
|-------|------|------|-------|
| `id` | int | — | Primary key |
| `name` | string | — | Sender full name |
| `company` | string\|null | — | Sender company (nullable) |
| `email` | string | — | Sender email |
| `phone` | string\|null | — | Sender phone (nullable) |
| `subject` | string\|null | — | Message subject |
| `enquiry_type` | string | `ContactEnquiryType` | Enum cast |
| `message` | text | — | Main message body |
| `attachments` | json | `array` | Array of attachment objects |
| `status` | string | `ContactStatus` | Enum cast |
| `priority` | string\|null | — | `Priority` enum value (not cast) |
| `assigned_to` | int\|null | — | FK → `users.id` |
| `internal_notes` | text\|null | — | Admin-only notes |
| `source` | string\|null | — | Origin channel (web, api, etc.) |
| `reply` | text\|null | — | Admin reply draft / sent text |
| `replied_at` | datetime\|null | `datetime` | Set when reply is sent |
| `read_at` | datetime\|null | `datetime` | Set on first drawer open |
| `ip_address` | string\|null | — | Submitter IP (not exposed in UI) |
| `user_agent` | string\|null | — | Submitter UA (not exposed in UI) |
| `created_at` | datetime | — | Submission timestamp |
| `updated_at` | datetime | — | Last update timestamp |

## Enum Values

### ContactStatus

| Value | Label | Badge Color |
|-------|-------|------------|
| `new` | New | primary |
| `in_progress` | In Progress | warning |
| `resolved` | Resolved | success |

### ContactEnquiryType

| Value | Label |
|-------|-------|
| `general` | General Enquiry |
| `sales` | Sales |
| `distributor` | Distributor |
| `quote` | Quote |
| `technical_support` | Technical Support |
| `other` | Other |

### Priority

| Value | Label | Badge Color |
|-------|-------|------------|
| `critical` | Critical | danger |
| `high` | High | warning |
| `medium` | Medium | primary |
| `low` | Low | info |
| `neutral` | Neutral | gray |

## getDetail() Return Shape

```php
[
    'id'               => int,
    'name'             => string,
    'company'          => string|null,
    'email'            => string,
    'phone'            => string|null,
    'subject'          => string|null,
    'enquiry_type'     => ContactEnquiryType|null,
    'enquiry_type_label' => string,
    'message'          => string|null,
    'attachments'      => array,
    'status'           => ContactStatus,
    'status_label'     => string,
    'status_color'     => string,
    'priority'         => string|null,
    'priority_label'   => string,
    'priority_color'   => string,
    'source'           => string|null,
    'internal_notes'   => string|null,
    'reply'            => string|null,
    'replied_at'       => Carbon|null,
    'read_at'          => Carbon|null,
    'created_at'       => Carbon|null,
    'updated_at'       => Carbon|null,
    'assignee'         => [
        'id'       => int,
        'name'     => string,
        'email'    => string,
        'initials' => string,
    ]|null,
]
```

## Export Column Mapping

| Column key | Label | Source |
|-----------|-------|--------|
| `name` | Name | `ContactMessage::name` |
| `company` | Company | `ContactMessage::company` |
| `email` | Email | `ContactMessage::email` |
| `phone` | Phone | `ContactMessage::phone` |
| `subject` | Subject | `ContactMessage::subject` |
| `enquiry_type` | Enquiry Type | `ContactEnquiryType::label()` |
| `status` | Status | `ContactMessage::statusLabel()` |
| `priority` | Priority | `ContactMessage::priorityLabel()` |
| `source` | Source | `ContactMessage::source` |
| `assigned_to` | Assigned To | `User::name` via relation |
| `replied_at` | Replied At | formatted `Y-m-d H:i:s` |
| `created_at` | Received At | formatted `Y-m-d H:i:s` |

## Filter → Query Mapping

| Filter key | Builder clause |
|-----------|----------------|
| `search` | `WHERE name LIKE % OR email LIKE % OR company LIKE % OR subject LIKE % OR message LIKE %` |
| `status` | `WHERE status IN (...)` |
| `source` | `WHERE source IN (...)` |
| `enquiry_type` | `WHERE enquiry_type IN (...)` |
| `priority` | `WHERE priority IN (...)` |
| `assigned_to` | `WHERE assigned_to = ?` |
| `date_from` | `WHERE DATE(created_at) >= ?` |
| `date_until` | `WHERE DATE(created_at) <= ?` |

## Sortable Fields

`name`, `company`, `email`, `status`, `priority`, `enquiry_type`, `source`, `updated_at`, `assigned_to` (subquery), `created_at` (default).
