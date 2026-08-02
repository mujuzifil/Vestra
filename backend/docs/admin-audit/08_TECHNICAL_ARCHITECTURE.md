# Technical Architecture Audit

## 1. Inventory Summary

| Layer | Count | Notes |
|-------|-------|-------|
| Filament Resources | 41 | Core admin CRUD surfaces |
| Custom Pages | 24 | 14 registered + 10 auto-discovered |
| Widgets | 20 | Dashboard, report, and utility widgets |
| Relation Managers | 15 | Mostly on `CustomerResource` and `DistributorResource` |
| Exporters | 4 | Customer, Order, PaymentTransaction, Product |
| Navigation Groups | 11 declared + 1 undeclared (`Content`) | |

## 2. Directory Structure

```
backend/app/Filament/
├── AdminPanelProvider.php
├── Pages/
│   ├── Dashboard.php                 # 17 widgets
│   ├── ReportsDashboard.php
│   ├── AdministrationDashboard.php
│   ├── Auth/ForcePasswordChange.php
│   ├── Administration/SystemHealth.php
│   ├── Administration/SecurityPolicies.php
│   ├── SettingsDashboard.php
│   ├── SystemInformation.php
│   ├── NotificationDashboard.php
│   └── Reports/... (14 report pages)
├── Resources/
│   ├── QuoteRequestResource.php
│   ├── DistributorRequestResource.php
│   ├── CustomerResource.php
│   ├── ProductResource.php
│   ├── BlogPostResource.php
│   ├── DistributorResource.php
│   ├── UserResource.php
│   ├── OrderResource.php
│   └── ... (~30 additional resources)
├── Widgets/
│   ├── ExecutiveKpiWidget.php
│   ├── OperationalKpiWidget.php
│   ├── RevenueChartWidget.php
│   ├── CustomerIntelligenceWidget.php
│   └── ...
└── Exports/
    ├── CustomerExporter.php
    ├── OrderExporter.php
    ├── PaymentTransactionExporter.php
    └── ProductExporter.php (unused)
```

## 3. Resources by Business Domain

### Sales / Requests
- `QuoteRequestResource` — public B2B quote requests.
- `DistributorRequestResource` — distributor applications.
- `ContactMessageResource`, `CustomerFeedbackResource` — inbound messages.

### Distributors
- `DistributorResource` (currently under Operations)
- `DistributorBranchResource`, `DistributorContactResource`, `DistributorDocumentResource`
- `DistributorPriceTierResource`, `DistributorProductPriceResource`
- `QuotationRequestResource` — distributor quotations

### Finance
- `CreditAccountResource`, `CreditTransactionResource`
- `PaymentTransactionResource`, `PaymentUploadResource`

### Catalog / Inventory
- `ProductResource`, `CategoryResource`
- `WarehouseResource`, `ProductWarehouseStockResource`, `StockMovementResource`

### CRM
- `CustomerResource` (model `User`, non-admin scope)
- `CustomerTagResource`

### Content
- `BlogPostResource`, `BlogCategoryResource`, `BlogTagResource`, `BlogAuthorResource`

### Administration / System
- `UserResource`, `RoleResource`, `PermissionResource`
- `AuditLogResource`, `LoginActivityResource`, `AdminSessionResource`
- `SettingResource`, `NotificationTemplateResource`, `NotificationDeliveryResource`, `AnnouncementResource`
- `AutomatedWorkflowResource`

### Legacy Commerce
- `OrderResource`, `ReviewResource`

## 4. Models, Policies, and Authorization

- Policies exist for most resources (`QuoteRequestPolicy`, `DistributorRequestPolicy`, `CustomerPolicy`).
- `UserResource` uses Spatie Permission for role/permission assignment.
- `RoleResource::isSystemRole()` protects `Super Administrator`, `Administrator`, `Manager`, and `customer` roles.
- Most resources implement `canAccess()` via `auth()->user()?->isAdmin()`, leading to global admin-or-nothing authorization rather than fine-grained policies.

## 5. Observers, Events, Listeners

| Area | Observed Components |
|------|---------------------|
| Quotes | Status-change events trigger notifications |
| Distributors | `DistributorOnboardingService` handles approval provisioning |
| Customers | Welcome/account notifications |
| Orders | Legacy observers still registered |

Events/listeners referenced:

- `QuoteSubmitted`, `QuoteStatusChanged`
- `DistributorApplicationSubmitted`, `DistributorApplicationReviewed`
- `ContactMessageReceived`
- `NotificationCreated`

Jobs:

- `ProcessQuoteNotificationJob`
- `SendDistributorNotificationJob`
- `ProcessContactEnquiryJob`
- Queued Mailables

## 6. Services

- `DistributorOnboardingService` — application processing, status transitions, account provisioning.
- `OrderStatusService` — legacy order state handling.
- `AuditService` — admin action logging.
- `ReportService` / `ForecastingService` / `ApiAnalyticsService` — report data.
- Some model-specific logic remains in resources and controllers.

## 7. Notifications & Mailables

- Admin notifications via Filament database notifications.
- Customer email confirmations for Quote, Distributor, Contact.
- Mailables: `QuoteConfirmation`, `DistributorConfirmation`, `ContactConfirmation`, `AdminNotification`.

## 8. Exports & Imports

- `CustomerExporter` — wired to `CustomerResource` bulk action.
- `OrderExporter` — wired to `OrderResource`.
- `PaymentTransactionExporter` — wired to `PaymentTransactionResource`.
- `ProductExporter` — defined but not wired; product export uses a custom CSV bulk action.

## 9. API Surface

- `/api/v1/account/*` — authenticated customer business portal.
- `/api/v1/admin/*` — minimal admin AJAX endpoints where exposed.
- Filament resources provide their own routes under `/admin`.

## 10. Key Architectural Strengths

- Clear separation between public APIs and admin resources.
- Use of Filament v3 conventions enables rapid resource development.
- Service classes exist for core workflows (distributor onboarding, reporting).
- RBAC via Spatie Permission is in place.
- Strong audit logging and login activity tracking.

## 11. Key Architectural Weaknesses

- Legacy commerce resources remain in the codebase, adding noise.
- Some business logic is duplicated between resources and services.
- Global admin gate reduces authorization granularity.
- Relation managers are underused on some resources; related data often requires navigating away.
- Custom pages are not consistently organized by functional domain.
- Placeholder actions exist on several resources.
