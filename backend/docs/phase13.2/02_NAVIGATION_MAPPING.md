# Phase 13.2 — Navigation Mapping

## Retained & Remapped Resources

| Old Group | Old Label | New Group | New Label | File |
|-----------|-----------|-----------|-----------|------|
| Sales | Customers | Sales | Companies | CustomerResource.php |
| Sales | Quote Requests | Sales | Quotes | QuoteRequestResource.php |
| Distributors | Distributor Requests | Distributors | Applications | DistributorRequestResource.php |
| Distributors | Distributors | Distributors | Active Partners | DistributorResource.php |
| Distributors | Branches | Distributors | Territories | DistributorBranchResource.php |
| Distributors | Credit Accounts | Distributors | Credit | CreditAccountResource.php |
| Customer Success | Contact Messages | Customer Success | Enquiries | ContactMessageResource.php |
| Customer Success | Customer Feedbacks | Customer Success | Feedback | CustomerFeedbackResource.php |
| Products | Products | Products | Products | ProductResource.php |
| Products | Categories | Products | Categories | CategoryResource.php |
| Products | Stock Movements | Products | Inventory | StockMovementResource.php |
| Products | Warehouses | Products | Warehouses | WarehouseResource.php |
| Operations | Suppliers | Operations | Suppliers | SupplierResource.php |
| Operations | Purchase Orders | Operations | Purchase Orders | PurchaseOrderResource.php |
| Operations | Automated Workflows | Operations | Workflows | AutomatedWorkflowResource.php |
| Marketing | Blog Posts | Marketing | Blog | BlogPostResource.php |
| Administration | Users | Administration | Staff | UserResource.php |
| Administration | Roles | Administration | Roles | RoleResource.php |
| Administration | Settings | Administration | Settings | SettingResource.php |
| Administration | Audit Logs | Administration | Audit | AuditLogResource.php |
| Communications | Notification Templates | Communications | Templates | NotificationTemplateResource.php |
| Communications | Notification Deliveries | Communications | Notifications | NotificationDeliveryResource.php |
| Communications | Announcements | Communications | Campaigns | AnnouncementResource.php |

## Hidden from Navigation

These resources remain available at their direct URLs and via relation managers but no longer appear in the sidebar:

- OrderResource
- ReviewResource
- CustomerTagResource
- CreditTransactionResource
- PaymentTransactionResource
- PaymentUploadResource
- ProductWarehouseStockResource
- DistributorProductPriceResource
- DistributorPriceTierResource
- DistributorDocumentResource
- DistributorContactResource
- QuotationRequestResource
- AdminSessionResource
- LoginActivityResource
- PermissionResource
- BlogAuthorResource
- BlogCategoryResource
- BlogTagResource

## Hidden Legacy Pages

These pages remain registered but are hidden from navigation:

- NotificationDashboard (replaced by Workspace > Notifications placeholder)
- AdministrationDashboard
- SecurityPolicies
- SystemHealth
- SettingsDashboard
- SystemInformation
- ReportsDashboard and all report pages
- SearchAnalytics

## New Placeholder Pages

| Group | Label | Class | Slug |
|-------|-------|-------|------|
| Workspace | Tasks | Workspace\TasksPage | tasks |
| Workspace | Notifications | Workspace\NotificationsPage | workspace/notifications |
| Workspace | Activity | Workspace\ActivityPage | activity |
| Sales | Pipeline | Sales\PipelinePage | sales/pipeline |
| Sales | Opportunities | Sales\OpportunitiesPage | sales/opportunities |
| Customer Success | Support | CustomerSuccess\SupportPage | customer-success/support |
| Marketing | Media | Marketing\MediaPage | marketing/media |
| Marketing | SEO | Marketing\SeoPage | marketing/seo |
| Analytics | Executive | Analytics\ExecutiveAnalyticsPage | analytics/executive |
| Analytics | Sales | Analytics\SalesAnalyticsPage | analytics/sales |
| Analytics | Operations | Analytics\OperationsAnalyticsPage | analytics/operations |
| Analytics | Finance | Analytics\FinanceAnalyticsPage | analytics/finance |
| Administration | Integrations | Administration\IntegrationsPage | administration/integrations |
