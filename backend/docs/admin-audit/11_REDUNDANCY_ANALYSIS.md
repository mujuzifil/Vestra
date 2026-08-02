# Redundancy Analysis

## 1. Objective

Identify duplicated, obsolete, or low-value admin artifacts so that Admin Portal v3.0 can consolidate or retire them without losing functionality.

## 2. Legacy Commerce Artifacts

The platform was previously e-commerce oriented. The following artifacts still appear in the admin portal but no longer match the corporate B2B model:

| Artifact | Current State | Recommendation |
|----------|---------------|----------------|
| `OrderResource` | Manages retail orders | Archive or remove; replace with read-only history if data must be retained |
| `ReviewResource` | Product review moderation | Remove; B2B site does not use public reviews |
| `E-Commerce` navigation group | Holds legacy commerce resources | Dissolve group; archive or delete contents |
| Commerce settings group (`COMMERCE`) | Configuration for retail features | Remove or migrate to B2B settings |
| Cart/Coupon related code | Not exposed in navigation but may exist | Remove dead code |

## 3. Duplicated or Overlapping Concepts

### 3.1 User vs Customer Administration

- `UserResource` manages staff/admin accounts.
- `CustomerResource` manages business customers (same `User` model, non-admin scope).
- Both handle names, emails, passwords, roles/permissions, and statuses.
- **Opportunity**: extract common profile/address/security patterns into shared components while keeping domains separate.

### 3.2 Orders vs Invoices

- `DistributorResource` registers both `OrdersRelationManager` and `InvoicesRelationManager` against the same `orders` relationship. The only difference is a `whereNotNull('invoice_number')` filter on invoices.
- **Opportunity**: merge into a single relation with a status/type filter, or create a dedicated `Invoice` model.

### 3.3 Quote vs Quotation

- `QuoteRequestResource` — public B2C/B2B quote requests from the website.
- `QuotationRequestResource` — distributor quotation workflow.
- **Opportunity**: differentiate navigation labels (e.g., “Public Quote Requests” vs “Distributor Quotations”) or unify models if business logic permits.

### 3.4 Notifications

- Filament database notifications.
- Custom `NotificationDashboard` page.
- `NotificationTemplateResource`, `NotificationDeliveryResource`, `AnnouncementResource`.
- **Opportunity**: unify notification architecture; dashboard should summarize rather than duplicate resource data.

### 3.5 Settings

- `SettingsDashboard` page.
- `SystemInformation` page.
- `SettingResource` with 11 hidden group edit pages.
- **Opportunity**: merge into a single Site Configuration area grouped by domain (company, SEO, social, integrations, security).

### 3.6 Reports Dashboards

- `ReportsDashboard` lists 14 report pages.
- Many report pages overlap conceptually (Sales/Revenue, Customer/Customer Intelligence, Inventory/Inventory Intelligence, Distributor/Distributor Intelligence).
- **Opportunity**: consolidate into one Analytics workspace with nested sections.

## 4. Low-Value or Unused Pages

- Default dashboard pages that only display placeholder widgets.
- Legacy profile pages superseded by Business Portal.
- Unused import stubs.
- `ProductExporter` is defined but not wired to `ProductResource`.

## 5. Placeholder Actions (Functional Redundancy / Dead Weight)

| Resource | Placeholder Action |
|----------|--------------------|
| `CustomerResource` | Send Email bulk action |
| `OrderResource` | Print Invoices bulk action |
| `DistributorRequestResource` | Assign reviewer, export, archive |
| `ContactMessageResource` | Assign administrator, archive |
| `CustomerFeedbackResource` | Assign administrator, archive |
| `UserResource` | Send invitation |
| `QuotationRequestResource` | Convert to Order (only updates status) |
| `AuditLogResource` | Export CSV (dead link) |

These should be implemented or removed before v3.0.

## 6. Consolidation Candidates

| From | To | Rationale |
|------|----|-----------|
| UserResource + RoleResource + PermissionResource | Administration → Staff & Access | Single location for identity and access management |
| ReportsDashboard + 14 report pages | Analytics workspace | Unified reporting destination |
| SettingsDashboard + SettingResource + SystemInformation | Site Configuration group | Easier maintenance |
| NotificationDashboard + NotificationDeliveryResource + AnnouncementResource | Communications hub | Consistent notification experience |
| CustomerResource company fields | Future CompanyResource | Proper CRM-style account hierarchy |
| Orders + Invoices relation managers | Single relation or Invoice model | Remove duplication |

## 7. Safe Removal Checklist

Before removing any artifact:

1. Verify no frontend URLs or APIs reference it.
2. Archive data via export or migration to history tables.
3. Update documentation and navigation.
4. Run regression tests on dependent workflows.
5. Remove routes, policies, menu items, and translations.

## 8. Estimated Impact

Consolidating legacy commerce resources and duplicated settings could reduce:

- Navigation groups by 2–3.
- Resources by 8–12.
- Custom pages by 5–8.
- Placeholder actions to zero.

This would significantly simplify the admin experience without removing active B2B functionality.
