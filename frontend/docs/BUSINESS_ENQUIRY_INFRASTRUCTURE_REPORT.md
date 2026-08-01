# VESTRA Business Enquiry Infrastructure Report

**Phase:** 1.5 — Business Enquiry Infrastructure Completion  
**Date:** 2026-08-01

---

## 1. Overview

The VESTRA public website has been restructured from an e-commerce storefront into a B2B corporate enquiry platform. Phase 1.5 completes the core infrastructure so that every public CTA is functional.

The primary business workflow is now:

```
Visitor → Request a Quote → Backend persistence → Sales notification
                                    ↓
                         Customer confirmation email
                                    ↓
                         Admin management in Filament
```

---

## 2. Components Implemented

### 2.1 Public Quote Request API

- **Endpoint:** `POST /api/v1/quote-requests`
- **Throttle:** `contact` middleware
- **Controller:** `App\Http\Controllers\Api\V1\QuoteRequestController`
- **Service:** `App\Services\QuoteRequestService`
- **Validation:** `App\Http\Requests\Api\V1\StoreQuoteRequestRequest`
- **Resource:** `App\Http\Resources\V1\QuoteRequestResource`
- **Policy:** `App\Policies\QuoteRequestPolicy`

### 2.2 Database

New migrations:
- `2026_08_01_000001_create_quote_requests_table.php`
- `2026_08_01_000002_create_quote_request_items_table.php`

New models:
- `App\Models\QuoteRequest`
- `App\Models\QuoteRequestItem`

New enum:
- `App\Enums\QuoteRequestStatus` (Pending, Contacted, Quoted, Approved, Declined, Closed)

### 2.3 Notifications & Email

- **Event:** `App\Events\Notification\QuoteRequestSubmitted`
- **Internal notification:** Dispatched to all admin users via the existing notification engine (`quote_request.admin_notification` template).
- **Customer email:** `App\Mail\QuoteRequestReceivedMail` sent directly to the requester.
- **Template:** Added to `NotificationTemplateSeeder`.

### 2.4 Admin Management

- **Resource:** `App\Filament\Resources\QuoteRequestResource`
- **Navigation:** Requests → Quote Requests
- **Capabilities:**
  - List, view, edit
  - Filter by status, assigned user, submitted date
  - Status actions: Mark Contacted, Mark Quoted, Mark Approved, Mark Declined, Mark Closed
  - Bulk actions
  - Assign to admin user
  - Add admin notes

### 2.5 Frontend

- **Page:** `/request-quote`
- **API client:** `frontend/lib/api/quote-requests.ts`
- **Form fields:**
  - Full Name
  - Business / Organisation
  - Email
  - Phone
  - District / City
  - Physical Address
  - Preferred Delivery Date
  - Delivery Location
  - Product of Interest
  - Package Size
  - Estimated Quantity
  - Item Notes
  - Additional Requirements
- **Success state:** Shows reference number and CTAs (Return Home, View Products, Become a Distributor).

---

## 3. Data Captured

Each quote request records:

| Category | Fields |
|----------|--------|
| Customer | full_name, company_name, email, phone |
| Location | district, city, address, delivery_location |
| Timing | preferred_delivery_date |
| Products | items (product_name, product_id, package_size, quantity, notes) |
| Metadata | reference_number, source, ip_address, user_agent, submitted_at |
| Admin | status, assigned_to, admin_notes |

---

## 4. Integration Points

| System | Integration |
|--------|-------------|
| Notification Engine | `QuoteRequestSubmitted` event → `DispatchNotificationListener` |
| Email | Direct Mailable to requester; notification engine email to admins |
| Filament | `QuoteRequestResource` for sales/admin management |
| Frontend | `createQuoteRequest()` via `@tanstack/react-query` mutation |

---

## 5. Security

- Public endpoint is throttled.
- Admin resource is gated by `isAdmin()`.
- Validation rules prevent invalid data and excessively large quantities.
- IP address and user agent are stored for audit purposes.

---

## 6. Remaining Dependencies / Cleanup

- The obsolete cart/checkout backend subsystem remains in place. Removal is documented in `backend/docs/COMMERCE_CLEANUP_PLAN.md` and blocked by `OrderService` / `DistributorOrderService` dependencies.
- Cart-related TypeScript interfaces were removed from the frontend.
- The `/track` route now redirects to `/account/orders`.

---

## 7. Files Added / Modified

### Backend
- `backend/app/Enums/QuoteRequestStatus.php`
- `backend/app/Models/QuoteRequest.php`
- `backend/app/Models/QuoteRequestItem.php`
- `backend/app/Http/Controllers/Api/V1/QuoteRequestController.php`
- `backend/app/Http/Requests/Api/V1/StoreQuoteRequestRequest.php`
- `backend/app/Http/Resources/V1/QuoteRequestResource.php`
- `backend/app/Http/Resources/V1/QuoteRequestItemResource.php`
- `backend/app/Services/QuoteRequestService.php`
- `backend/app/Policies/QuoteRequestPolicy.php`
- `backend/app/Events/Notification/QuoteRequestSubmitted.php`
- `backend/app/Mail/QuoteRequestReceivedMail.php`
- `backend/app/Filament/Resources/QuoteRequestResource.php`
- `backend/app/Filament/Resources/QuoteRequestResource/Pages/*.php`
- `backend/app/Providers/EventServiceProvider.php`
- `backend/app/Providers/AuthServiceProvider.php`
- `backend/app/Listeners/Notification/DispatchNotificationListener.php`
- `backend/database/migrations/2026_08_01_000001_create_quote_requests_table.php`
- `backend/database/migrations/2026_08_01_000002_create_quote_request_items_table.php`
- `backend/database/factories/QuoteRequestFactory.php`
- `backend/database/factories/QuoteRequestItemFactory.php`
- `backend/database/seeders/NotificationTemplateSeeder.php`
- `backend/routes/api.php`
- `backend/resources/views/emails/quote-request/received.blade.php`
- `backend/tests/Feature/Api/V1/QuoteRequestControllerTest.php`

### Frontend
- `frontend/lib/api/quote-requests.ts`
- `frontend/app/request-quote/request-quote-page-client.tsx`
- `frontend/types/index.ts`
- `frontend/next.config.ts`

### Documentation
- `frontend/docs/BUSINESS_ENQUIRY_INFRASTRUCTURE_REPORT.md`
- `frontend/docs/QUOTE_WORKFLOW_DOCUMENTATION.md`
- `frontend/docs/VALIDATION_REPORT.md`
- `frontend/docs/TERMINOLOGY_AUDIT.md`
- `frontend/docs/DEAD_CODE_AUDIT.md`
- `backend/docs/COMMERCE_CLEANUP_PLAN.md`
- `backend/docs/DATABASE_RETENTION_ASSESSMENT.md`
