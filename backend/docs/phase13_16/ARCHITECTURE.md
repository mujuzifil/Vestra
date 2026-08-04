# Phase 13.16 — Backend Architecture

## FeedbackAdminService

Location: `app/Services/Admin/FeedbackAdminService.php`

### Methods

| Method | Description |
|---|---|
| `paginateFeedback(filters, sort, direction, perPage)` | Returns paginated `CustomerFeedback` with user eager-loaded |
| `queryFeedback(filters, sort, direction)` | Returns `Builder` with all filters applied |
| `getKpiCards()` | Returns 6 KPI cards (Total, New, In Progress, Resolved, Praise, Complaints) |
| `getDetail(CustomerFeedback)` | Returns normalized detail array for drawer |
| `exportRows(filters)` | Returns array of rows for CSV/Excel/PDF export |

### Filter Keys

```php
[
    'search'     => string|null,   // subject, message, user name/email
    'status'     => string[],      // FeedbackStatus values
    'category'   => string[],      // FeedbackCategory values
    'priority'   => string[],      // Priority values
    'date_from'  => string|null,   // Y-m-d
    'date_until' => string|null,   // Y-m-d
]
```

## FeedbackPage

Location: `app/Filament/Pages/CustomerSuccess/FeedbackPage.php`

Livewire component (Filament Page) using `WithPagination`. URL state managed via `#[Url]` attributes.

### Computed Properties

| Property | Returns |
|---|---|
| `$feedback` | Paginated `LengthAwarePaginator` |
| `$kpiCards` | Array of 6 KPI card arrays |
| `$selectedFeedback` | Detail array for drawer, or null |

### Actions

| Method | Description |
|---|---|
| `openDetailDrawer(id)` | Opens drawer, marks feedback as read |
| `closeDetailDrawer()` | Closes drawer |
| `markRead(id)` | Sets `read_at = now()` |
| `markUnread(id)` | Clears `read_at` |
| `markInProgress(id)` | Sets status = in_progress |
| `markResolved(id)` | Sets status = resolved |
| `sortBy(field)` | Toggles sort field/direction |
| `resetFilters()` | Clears all filter state |
| `getExportUrl(format)` | Builds export URL with active filters |

## FeedbackExportController

Location: `app/Http/Controllers/Admin/FeedbackExportController.php`

Invokable controller. Gate: `viewAny CustomerFeedback` (admin-only). Delegates to `FeedbackAdminService::exportRows()` and `ReportExportService`.

## AdminPanelProvider Changes

```php
// Import added
use App\Filament\Pages\CustomerSuccess\FeedbackPage;
use App\Http\Controllers\Admin\FeedbackExportController;

// Page registered
FeedbackPage::class,

// Route registered in authenticatedRoutes()
Route::get('customer-success/feedback/export', FeedbackExportController::class)
    ->name('customer-success.feedback.export');
```

## Model Changes

`CustomerFeedback::$fillable` expanded:

```php
protected $fillable = [
    'user_id',
    'category',
    'subject',
    'message',
    'status',       // added
    'priority',     // added
    'read_at',      // added
];
```

No migrations. Columns already exist in `customer_feedback` table.

## Policy

`CustomerFeedbackPolicy` already defines `viewAny`, `view`, `update`. No new policy methods added.
