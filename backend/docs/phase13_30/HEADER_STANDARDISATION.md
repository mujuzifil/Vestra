# Header Standardisation — Phase 13.30

## Standard

Every redesigned Workspace page uses:

1. Custom CRM layout (`filament.layouts.crm`)
2. Custom `x-*.page-header` component (title + subtitle + toolbar)
3. Empty Filament heading via `getHeading(): ''`
4. **No** `<x-filament-panels::page>` wrapper

Rendered structure:

```
<Page Title>
<Page Subtitle>
```

## Pages Updated

| Group | Page |
|-------|------|
| Distributors | Active Partners, Territories, Credit |
| Customer Success | Support, Enquiries, Feedback |
| Products | Products, Categories, Inventory |
| Marketing | Blog, Media |
| Administration | Staff, Roles |

## Reference Pages (already compliant)

Dashboard, Companies, Quotes, Tasks, Activity, Applications
