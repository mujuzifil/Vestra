# Phase 13.2 — Resource Reorganization

## What Was Changed

Only navigation properties were modified on existing Filament resources. No models, controllers, services, repositories, policies, forms, tables, or relation managers were changed.

## Method

For each retained resource, the following static properties were updated:
- `navigationGroup`
- `navigationLabel`
- `navigationIcon`
- `navigationSort`

For each legacy/out-of-scope resource, the following was added:
```php
protected static bool $shouldRegisterNavigation = false;
```

This preserves direct access URLs and relation managers while removing the item from the sidebar.

## Why These Resources Were Hidden

| Resource | Reason |
|----------|--------|
| OrderResource | Legacy e-commerce order management no longer part of B2B CRM workflow |
| ReviewResource | Legacy customer review system |
| CustomerTagResource | Supporting entity; managed inside customer detail |
| CreditTransactionResource | Supporting entity; visible via distributor credit account |
| PaymentTransactionResource | Legacy payment processing |
| PaymentUploadResource | Legacy distributor payment uploads |
| ProductWarehouseStockResource | Detail view; accessible via product/warehouse relations |
| DistributorProductPriceResource | Detail view; accessible via distributor relations |
| DistributorPriceTierResource | Detail view; accessible via distributor relations |
| DistributorDocumentResource | Detail view; accessible via distributor relations |
| DistributorContactResource | Detail view; accessible via distributor relations |
| QuotationRequestResource | Duplicate concept; QuoteRequestResource used instead |
| AdminSessionResource | Security audit detail |
| LoginActivityResource | Security audit detail |
| PermissionResource | Managed within Roles resource |
| BlogAuthorResource | Supporting entity; merged into Blog/SEO |
| BlogCategoryResource | Supporting entity; merged into Blog/SEO |
| BlogTagResource | Supporting entity; merged into Blog/SEO |

## Future Work

When new modules are built (Tasks, Pipeline, Opportunities, Support, Media, SEO, Analytics, Integrations), the placeholder pages should be replaced with real resources or enhanced custom pages.
