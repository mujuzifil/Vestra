# Phase 13.16 — Feedback Workspace

## Overview

Introduces an enterprise Feedback CRM workspace under the **Customer Success** navigation group, replacing the plain Filament resource list with a full CRM-shell experience.

## Architecture

| Layer | Component |
|---|---|
| Filament Page | `App\Filament\Pages\CustomerSuccess\FeedbackPage` |
| Service | `App\Services\Admin\FeedbackAdminService` |
| Export Controller | `App\Http\Controllers\Admin\FeedbackExportController` |
| Blade Page View | `filament/pages/customer-success/feedback.blade.php` |
| Blade Components | `components/feedback/*` (11 components) |
| CSS | `resources/css/filament/admin/components/feedback.css` |
| Tests | `tests/Feature/Admin/FeedbackPageTest.php` |

## Navigation

- Group: **Customer Success**
- Label: **Feedback**
- Icon: `heroicon-o-chat-bubble-left-right`
- Sort: **3**
- Slug: `customer-success/feedback`
- Layout: `filament.layouts.crm`

## Model

**`CustomerFeedback`** (table: `customer_feedback`)

Columns used: `user_id`, `category`, `subject`, `message`, `status`, `priority`, `read_at`.

No migrations required — only `$fillable` expanded to include `status`, `priority`, `read_at`.

### Enums

| Enum | Values |
|---|---|
| `FeedbackStatus` | `new`, `in_progress`, `resolved` |
| `FeedbackCategory` | `general`, `bug`, `feature`, `complaint`, `praise` |
| `Priority` | `critical`, `high`, `medium`, `low`, `neutral` |

## KPI Cards (FeedbackAdminService)

Six cards driven from live DB counts — compared against same period last month:

1. **Total** — all feedback
2. **New** — status = new
3. **In Progress** — status = in_progress
4. **Resolved** — status = resolved
5. **Praise** — category = praise
6. **Complaints** — category = complaint

No invented ratings, no sentiment scores.

## Filters

- Status (multi-select)
- Category (multi-select)
- Priority (multi-select)
- Date range (from/until)
- Search (subject, message, user name/email)

## Drawer

Slide-in detail drawer displays:
- Full message body
- Customer name and email
- Category, priority, status badges
- Quick-action buttons: In Progress, Resolve, Mark Read/Unread
- Timestamps (submitted, updated, read_at)

Opening the drawer auto-marks the item as read.

## Export

Route: `GET /customer-success/feedback/export?format={csv|excel|pdf}`

Named: `filament.admin.customer-success.feedback.export`

Gate: `viewAny` on `CustomerFeedback` (admin-only).

Columns: customer, email, category, subject, status, priority, read, submitted_at.

## Legacy Resource Handling

- `CustomerFeedbackResource::shouldRegisterNavigation()` → `false` (hidden from nav)
- `ListCustomerFeedback::mount()` issues a redirect to `FeedbackPage::getUrl()`
