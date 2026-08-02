# Recommendations

## 1. Strategic Recommendations

### 1.1 Reorganize Navigation by Business Function

Replace the current 11 navigation groups with a smaller set of business domains:

- **Workspace** — dashboard, notifications, tasks, recent activity.
- **Sales** — quote requests, distributor requests, customers, pipeline.
- **Marketing** — blog posts, contact messages, customer feedback, announcements.
- **Products** — products, categories, warehouses, stock, documents.
- **Reports** — analytics, KPIs, exports.
- **Administration** — users, roles, permissions, settings, audit logs, system health.

### 1.2 Remove Legacy Commerce Baggage

Archive or delete resources, pages, and navigation groups tied to the old e-commerce model:

- `OrderResource`, `ReviewResource`.
- `E-Commerce` navigation group.
- Commerce-specific settings group (`COMMERCE`).
- Cart/coupon dead code not exposed in navigation.

Data must be archived before removal where retention is required.

### 1.3 Consolidate User and Customer Administration

- Keep `UserResource` for staff and `CustomerResource` for business accounts.
- Extract common profile, address, security, and notification patterns into reusable components.
- Consider a future `CompanyResource` to represent business accounts with multiple contacts.

### 1.4 Resolve Distributor Domain Split

- Move `DistributorResource` from Operations into the Distributors group.
- Rename Operations to Procurement & Workflows if it only holds workflows, POs, and suppliers.
- Clarify `QuoteRequest` vs `QuotationRequest` navigation labels (e.g., “Public Quote Requests” and “Distributor Quotations”).

### 1.5 Build a Lightweight CRM

Add missing CRM capabilities in priority order:

1. Admin support ticket resource.
2. Unified activity timeline.
3. Quote pipeline / Kanban view.
4. Tasks and follow-ups.
5. Communication log per customer/quote.

### 1.6 Standardize the Component Library

Create shared Filament primitives:

- `StatusBadge`, `AvatarWithFallback`, `AttachmentList`, `Timeline`, `SeoPreviewCard`, `StatisticCard`, `EmptyStatePanel`, `CompanyHeader`.
- Apply consistent form spacing and grid defaults across all resources.

## 2. Tactical Recommendations

### 2.1 Performance

- Add composite indexes on `(status, created_at)` for high-volume tables.
- Cache dashboard aggregates for 5–15 minutes.
- Eager-load relationships in every table and infolist.
- Use async/deferred selects for customer/product/user/distributor relationships.
- Stream large exports.
- Resolve report page sort collisions and add date-range caching.

### 2.2 UX & Accessibility

- Apply the Phase 10 public design system colors, typography, and spacing to the admin panel.
- Ensure every table has a meaningful empty state.
- Standardize button hierarchy (primary, secondary, danger, ghost).
- Improve keyboard navigation and focus indicators.
- Add ARIA labels to custom components.
- Remove or implement all placeholder actions.

### 2.3 Workflow Improvements

- Add bulk actions for common transitions (approve/reject distributor applications, mark quotes as quoted).
- Introduce assignment workflows with notifications.
- Provide one-click PDF generation and email from quote detail pages.
- Add status history/timeline to quote and distributor views.
- Link contact messages to customers/quotes.

### 2.4 Reporting

- Consolidate dashboards into one Reports/Analytics workspace.
- Add pre-built filters (this week, this month, by representative, by district).
- Provide exportable reports for sales, distributor pipeline, and customer activity.

### 2.5 Quality Assurance

- Enforce policy-based authorization on all resources.
- Add feature tests for admin workflows.
- Introduce static analysis (PHPStan/Pint) if not already enforced.
- Monitor query performance in production.

## 3. Implementation Priority

| Priority | Initiative | Effort | Impact |
|----------|------------|--------|--------|
| Critical | Remove/archive legacy commerce resources | Low | High |
| Critical | Reorganize navigation into business domains | Medium | High |
| Critical | Remove or implement placeholder actions | Low | High |
| High | Build shared Filament component library | Medium | High |
| High | Add admin support ticket resource | Medium | High |
| High | Consolidate settings | Low | Medium |
| Medium | Implement dashboard caching and indexes | Low | High |
| Medium | Add activity timeline and quote pipeline | Medium | High |
| Medium | Improve UX/accessibility consistency | Medium | Medium |
| Low | Add tasks/follow-ups | Medium | Medium |
| Low | Advanced CRM analytics | High | Medium |

## 4. Success Metrics

- Navigation groups reduced to 6–7.
- Resource count reduced by 20–30%.
- Admin dashboard load time improved (target <1.5s for primary KPIs).
- Zero legacy commerce items in active navigation.
- Zero placeholder actions in active navigation.
- WCAG 2.2 AA compliance for new admin components.
- All critical workflows covered by automated tests.
