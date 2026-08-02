# Phase 13.0 — Page Catalogue

## Legend

- **UX Rating:** Excellent / Good / Average / Poor / Critical

## Core Dashboard

### Dashboard
- **Route:** `/`
- **Resource/Page:** `App\Filament\Pages\Dashboard`
- **Group:** (top-level)
- **Purpose:** Executive overview of revenue, orders, customers, inventory, distributors, and system health.
- **Widgets:** 17 widgets configured including KPIs, charts, recent orders, top distributors, alerts, activity.
- **UX Rating:** Average — large widget count creates information overload; no prioritization.
- **Data:** `Order`, `Customer`, `Distributor`, `Product` models, caches.

## Administration

### Administration Dashboard
- **Route:** `/administration`
- **Page:** `App\Filament\Pages\Administration\AdministrationDashboard`
- **Group:** Administration
- **Purpose:** Landing page for admin tools with search and shortcut cards.
- **UX Rating:** Good — clear shortcuts, but duplicates sidebar items.

### Users
- **Route:** `/users`
- **Resource:** `UserResource` (model: `User`, admin scope)
- **Group:** Administration
- **Purpose:** Manage admin users, roles, status, password resets.
- **Form:** Profile, Security, Roles sections.
- **Table:** Name, initials, email, department, roles, status, last login, 2FA placeholder.
- **UX Rating:** Average — 2FA column is a hardcoded false placeholder.

### Roles
- **Route:** `/roles`
- **Resource:** `RoleResource`
- **Group:** Administration
- **Purpose:** Role-based access control.
- **UX Rating:** Average — standard Filament resource.

### Permissions
- **Route:** `/permissions`
- **Resource:** `PermissionResource`
- **Group:** Administration
- **Purpose:** Browse permissions.
- **UX Rating:** Good — read-only reference.

### Audit Logs
- **Route:** `/audit-logs`
- **Resource:** `AuditLogResource`
- **Group:** Administration
- **Purpose:** Administrator action audit trail.
- **UX Rating:** Good — essential for compliance.

### Login Activity
- **Route:** `/login-activities`
- **Resource:** `LoginActivityResource`
- **Group:** Administration
- **Purpose:** Track login attempts.
- **UX Rating:** Good.

### Sessions
- **Route:** `/admin-sessions`
- **Resource:** `AdminSessionResource`
- **Group:** Administration
- **Purpose:** Active administrator sessions.
- **UX Rating:** Good.

### Security Policies
- **Route:** `/security-policies`
- **Page:** `App\Filament\Pages\Administration\SecurityPolicies`
- **Group:** Administration
- **Purpose:** Password policy, session timeout, login limits.
- **UX Rating:** Average — custom page, exact fields not audited.

### System Health
- **Route:** `/system-health`
- **Page:** `App\Filament\Pages\Administration\SystemHealth`
- **Group:** Administration
- **Purpose:** Database, cache, queue, storage, mail connectivity checks.
- **UX Rating:** Good.

## Catalog

### Products
- **Route:** `/products`
- **Resource:** `ProductResource`
- **Group:** Catalog
- **Purpose:** Manage product catalogue.
- **Form:** General, Pricing, Inventory, Description, Media, SEO, Publishing.
- **Table:** Image, name, category, SKU, price, stock status, status, featured, updated.
- **UX Rating:** Good — comprehensive but dense; some fields disabled as "planned".
- **Relations:** Product Warehouse Stocks.

### Categories
- **Route:** `/categories`
- **Resource:** `CategoryResource`
- **Group:** Catalog
- **Purpose:** Product categorisation.
- **UX Rating:** Good.

## Inventory

### Warehouses
- **Route:** `/warehouses`
- **Resource:** `WarehouseResource`
- **Group:** Inventory
- **Purpose:** Warehouse master data.
- **UX Rating:** Good.

### Product Warehouse Stocks
- **Route:** `/product-warehouse-stocks`
- **Resource:** `ProductWarehouseStockResource`
- **Group:** Inventory
- **Purpose:** Per-warehouse stock levels.
- **UX Rating:** Average — thin resource, mostly utility.

### Stock Movements
- **Route:** `/stock-movements`
- **Resource:** `StockMovementResource`
- **Group:** Inventory
- **Purpose:** Historical stock movements.
- **UX Rating:** Average.

## Distributors

### Distributors
- **Route:** `/distributors`
- **Resource:** `DistributorResource`
- **Group:** Operations (also appears here via `Distributors` group in some contexts)
- **Purpose:** Approved distributor accounts.
- **Form:** Minimal company fields only.
- **Table:** Company name, trading name, email, phone, status.
- **UX Rating:** Poor — form is extremely minimal compared to the rich distributor model; most data hidden.
- **Relations:** Credit Account, Branches, Contacts, Documents, Quotations, Orders, Invoices.

### Distributor Requests
- **Route:** `/distributor-requests`
- **Resource:** `DistributorRequestResource`
- **Group:** Requests
- **Purpose:** Review incoming distributor applications.
- **Form:** Application info, address, details, review decision.
- **Table:** Business name, applicant, phone, country, region, status, priority, assigned admin.
- **Actions:** Approve, reject, request info, return to review.
- **UX Rating:** Good — strong workflow actions, but several bulk actions are placeholders.

### Branches / Contacts / Documents / Price Tiers / Product Prices
- **Purpose:** Supporting master data for distributors.
- **UX Rating:** Average — standard supporting resources, some likely thin.

### Quotations
- **Route:** `/quotation-requests`
- **Resource:** `QuotationRequestResource`
- **Group:** Distributors
- **Purpose:** Distributor quotation workflow.
- **UX Rating:** Average — overlaps conceptually with `Quote Requests`.

## Finance

### Credit Accounts
- **Route:** `/credit-accounts`
- **Resource:** `CreditAccountResource`
- **Group:** Finance
- **Purpose:** Distributor credit limits.
- **UX Rating:** Average.

### Credit Transactions
- **Route:** `/credit-transactions`
- **Resource:** `CreditTransactionResource`
- **Group:** Finance
- **Purpose:** Credit account transactions.
- **UX Rating:** Average.

### Payment Transactions
- **Route:** `/payment-transactions`
- **Resource:** `PaymentTransactionResource`
- **Group:** Finance
- **Purpose:** Payment gateway transaction log.
- **UX Rating:** Average.

### Payment Uploads
- **Route:** `/payment-uploads`
- **Resource:** `PaymentUploadResource`
- **Group:** Finance
- **Purpose:** Distributor payment receipt uploads.
- **UX Rating:** Average.

## CRM

### Customers
- **Route:** `/customers`
- **Resource:** `CustomerResource` (model: `User`, non-admin scope)
- **Group:** CRM
- **Purpose:** Customer directory with lifetime metrics.
- **Table:** Avatar, name, phone, registered, lifetime orders, lifetime spend, last order, status.
- **UX Rating:** Average — metrics are useful but tied to e-commerce orders that may be unused.
- **Relations:** Notes, Tags, Addresses, Orders, Payments, Audit Logs.

### Customer Tags
- **Route:** `/customer-tags`
- **Resource:** `CustomerTagResource`
- **Group:** CRM
- **Purpose:** Tag customers for segmentation.
- **UX Rating:** Good.

## Requests

### Quote Requests
- **Route:** `/quote-requests`
- **Resource:** `QuoteRequestResource`
- **Group:** Requests
- **Purpose:** Manage B2B quote requests.
- **Form:** Customer info, delivery, items, CRM fields, attachments, admin handling.
- **Table:** Reference, full name, company, email, status, assigned to, submitted.
- **Actions:** Mark contacted, quoted, approved, declined, closed.
- **UX Rating:** Good — clean workflow for the primary B2B conversion.

### Contact Messages
- **Route:** `/contact-messages`
- **Resource:** `ContactMessageResource`
- **Group:** Requests
- **Purpose:** Contact form submissions.
- **UX Rating:** Average.

### Customer Feedbacks
- **Route:** `/customer-feedback`
- **Resource:** `CustomerFeedbackResource`
- **Group:** Requests
- **Purpose:** Customer feedback submissions.
- **UX Rating:** Average.

## Reports

### Reports Dashboard
- **Route:** `/reports`
- **Page:** `ReportsDashboard`
- **Group:** Reports
- **Purpose:** Directory of 14 report pages.
- **UX Rating:** Good — consistent card grid.

### Individual Report Pages
- Revenue, Sales, Customers, Inventory, Engagement, Distributors, Procurement, Credit, Forecasting, Customer Intelligence, Distributor Intelligence, Inventory Intelligence, API Analytics, Operational Monitoring, Search Analytics.
- **UX Rating:** Average — many report pages extend `ReportPage` base; actual complexity varies.

## System

### Settings
- **Route:** `/settings`
- **Page:** `SettingsDashboard`
- **Group:** System
- **Purpose:** Grouped settings entry point.
- **UX Rating:** Good.

### System Information
- **Route:** `/system-information`
- **Page:** `SystemInformation`
- **Group:** System
- **UX Rating:** Good.

### Notification Dashboard
- **Route:** `/notification-dashboard`
- **Page:** `NotificationDashboard`
- **Group:** System
- **Purpose:** Notification overview.
- **UX Rating:** Average.

### Notification Templates / Deliveries / Announcements
- **Purpose:** Notification infrastructure management.
- **UX Rating:** Average.

## E-Commerce (Legacy)

### Orders
- **Route:** `/orders`
- **Resource:** `OrderResource`
- **Group:** E-Commerce
- **Purpose:** Retail order lifecycle.
- **UX Rating:** Poor — full order workflow remains but retail e-commerce is no longer the primary business model.

### Reviews
- **Route:** `/reviews`
- **Resource:** `ReviewResource`
- **Group:** E-Commerce
- **Purpose:** Product reviews moderation.
- **UX Rating:** Poor — reviews are no longer part of the corporate B2B experience.

## Content

### Blog Posts / Categories / Tags / Authors
- **Purpose:** Knowledge Centre CMS.
- **UX Rating:** Good — rich editor, SEO fields, publishing controls.
