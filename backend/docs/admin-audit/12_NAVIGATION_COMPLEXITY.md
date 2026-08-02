# Navigation Complexity Analysis

## 1. Quantitative Inventory

| Category | Count |
|----------|-------|
| Declared Navigation Groups | 11 |
| Undeclared Navigation Groups | 1 (`Content`) |
| Filament Resources | 41 |
| Custom Pages | 24 |
| Widgets | 20 |
| Relation Managers | 15 |
| Dashboard Cards / KPIs | ~17 |
| Report Pages | 14 (+ Search Analytics) |
| Export Actions | 4 |

## 2. Navigation Groups

Groups as declared in `AdminPanelProvider`:

1. E-Commerce
2. Catalog
3. Inventory
4. Distributors
5. Finance
6. CRM
7. Operations
8. Requests
9. Reports
10. Administration
11. System

Blog resources use an undeclared `Content` group, causing unpredictable ordering.

## 3. Depth and Discoverability

- Most resources are one click from the sidebar.
- Some actions require navigating through relation managers or separate view/edit pages.
- Reports are split across 15 pages in one group, increasing cognitive load.
- Settings are fragmented across several resources/pages.
- `DistributorResource` appears under Operations while all distributor master-data resources appear under Distributors, splitting the domain.

## 4. Sort Collisions

Several report pages share the same `navigationSort` value:

- `50`: Engagement, Procurement
- `60`: Distributors, Credit, Forecasting
- `71`: Operational Monitoring, API Analytics (adjacent but distinct)

These collisions make report ordering non-deterministic.

## 5. 80/20 Value Estimate

Approximately **15% of resources** likely deliver **80% of daily business value**:

1. **Quote Requests** — core sales workflow.
2. **Distributor Requests** — partner onboarding.
3. **Customers** — account management.
4. **Products** — catalogue management.
5. **Blog Posts** — content marketing.
6. **Contact Messages** — inbound enquiries.

Administrative and configuration resources are necessary but used less frequently.

## 6. Cognitive Load Indicators

- Legacy `E-Commerce` group adds irrelevant noise.
- Similar concepts are split (`Reports` vs `Intelligence` reports, `Quote Requests` vs `Quotation Requests`).
- Some resources have overlapping names (`Settings` page + `Settings` resource, `System` group + `SystemInformation` page).
- Badges and counters are not consistently used to highlight work requiring attention.

## 7. Recommendations for Simplification

1. **Collapse groups** into 5–7 business domains:
   - **Workspace** — dashboard, notifications, tasks, recent activity.
   - **Sales** — quotes, distributors, customers.
   - **Marketing** — blog, content, SEO, contacts/feedback.
   - **Products** — catalogue, categories, inventory, documents.
   - **Reports** — analytics, exports.
   - **Administration** — staff, roles, settings, logs.
2. **Remove or archive** the E-Commerce group and legacy resources.
3. **Move `DistributorResource`** into the Distributors group.
4. **Declare `Content`** explicitly or merge into Marketing.
5. **Use badges** on Sales and Communication groups to surface pending items.
6. **Pin high-value resources** to the top of the sidebar.
7. **Provide a global search** shortcut for records and actions.
8. **Group settings** under a single expandable section.
9. **Resolve report sort collisions** and introduce nested report categories.

## 8. Target Complexity

After consolidation:

- Navigation groups: 6–7
- Resources: 28–32
- Custom pages: 15–18
- Widgets: 12–15 reusable widgets
- Report pages: consolidated into a smaller set with filters

This represents a **~25–30% reduction** in navigation items while preserving all active functionality.
