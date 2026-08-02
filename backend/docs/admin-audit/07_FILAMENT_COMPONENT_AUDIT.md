# Filament Component Audit

## 1. Overview

The Admin Portal is built on Filament v3. Most interaction primitives are standard Filament components, while a smaller set of custom components support VESTRA-specific workflows. This audit documents default usage, custom components, duplication, and the shared primitives needed for Admin Portal v3.0.

**Inventory from exploration:**
- 41 resources
- 24 custom pages (14 registered + 10 auto-discovered)
- 20 widget classes
- 15 relation managers
- 4 exporters

## 2. Default Filament Components in Use

### 2.1 Forms

- `TextInput`, `Textarea`, `RichEditor`, `MarkdownEditor`
- `Select`, `MultiSelect`, `TagsInput`, `Checkbox`, `Toggle`, `Radio`, `CheckboxList`
- `DatePicker`, `DateTimePicker`, `TimePicker`
- `FileUpload`, `SpatieMediaLibraryFileUpload`
- `Repeater`, `Builder`, `Section`, `Fieldset`, `Tabs`, `Wizard`
- `Hidden`, `Placeholder`

These are used without a shared wrapper, causing duplicated validation messages, helper text, and spacing overrides.

### 2.2 Tables

- `TextColumn`, `BadgeColumn`, `IconColumn`, `ImageColumn`, `ToggleColumn`
- `Filter`, `SelectFilter`, `DateRangeFilter`, `TextFilter`
- `BulkAction`, `HeaderAction`, `Action` on rows
- `ExportAction` / `ImportAction`

Most resources expose standard tables. Styling overrides are minimal, so the admin experience looks different from the corporate public site.

### 2.3 Infolists

- `TextEntry`, `BadgeEntry`, `ImageEntry`, `RepeatableEntry`, `Section`

Used in View pages for `QuoteRequest`, `DistributorRequest`, `Customer`, and `Distributor`.

### 2.4 Notifications

- Filament `Notification::make()` for save/delete feedback.
- Database notifications surfaced via `NotificationDashboard` and `AnnouncementResource`.
- Customer-facing email notifications implemented as Mailables + queue jobs.

### 2.5 Navigation & Layout

- `NavigationGroup`, `NavigationItem`, `Sidebar`, `Topbar`
- `Page` and `Resource` base classes
- Custom `AdminPanelProvider` configures brand, colors, logo, 11 declared navigation groups, and plugin registration.

## 3. Custom Components

| Component | Location / Usage | Purpose | Reusable? |
|-----------|------------------|---------|-----------|
| Quote request attachment viewer | `QuoteRequestResource` view | Renders uploaded files with secure download links | Yes — generic attachment list |
| Customer avatar | `CustomerResource` table/view | Displays customer profile image / initials | Yes — avatar primitive |
| Distributor applicant card | `DistributorRequestResource` view | Highlights applicant identity and business details | Partial |
| SEO preview card | `BlogPostResource`, setting pages | Previews search/social snippet | Yes |
| Status badge helpers | Inline closures across resources | Maps status enums to color/badge combos | Yes — should become `StatusBadge` |
| Timeline / activity feed | Custom views | Shows quote/distributor status history | Yes — timeline primitive |
| `ExecutiveKpiWidget` | `app/Filament/Widgets/` | Aggregated KPI cards on dashboard | Partial — hard-coded metrics |
| Report chart widgets | `ReportsDashboard`, report pages | Bar/line charts and stat cards | Yes — chart primitives |
| `QuickActionsWidget` | Dashboard | Hard-coded shortcut buttons | Partial |
| `AlertsWidget` | Dashboard | Conditional alert cards | Yes — alert panel |

## 4. Reusable Primitives Needed for v3.0

1. **StatusBadge** — status enum → color/icon mapping used across Quote, Distributor, Contact, Support, Order.
2. **AvatarWithFallback** — customer, user, distributor representative.
3. **AttachmentList** — secure download links, file type icons, size, upload date.
4. **Timeline / ActivityFeed** — status changes, notes, assignments, replies.
5. **SeoPreviewCard** — for any content with meta title/description.
6. **StatisticCard** — consistent KPI presentation.
7. **EmptyStatePanel** — for tables and dashboards without data.
8. **CompanyHeader** — company name, type, location, contact, account status.

## 5. Component Consistency Issues

- **No centralized status color mapping.** Some statuses use `Color::Gray`, others use hex strings.
- **Avatar fallbacks inconsistent.** Some resources show initials, others leave blank space.
- **Attachment rendering duplicated.** Quote attachments, distributor documents, blog images each implement their own display logic.
- **Charts lack unified data format.** Report widgets transform data inline; switching libraries would touch every widget.
- **Form spacing and grid columns vary** between resources, producing uneven density.
- **Placeholder actions** (Send Email, Print Invoices, Export CSV, Convert to Order) are scattered across tables; they degrade trust and should be removed or implemented.

## 6. Recommendations

1. Create `app/Filament/Components/` namespace for shared VESTRA primitives.
2. Replace inline status badge closures with a single `StatusBadge::make()` component backed by enum metadata.
3. Refactor attachment viewers into a single `AttachmentList` infolist/table component.
4. Introduce a `Timeline` component used by Quotes, Distributor Applications, Support Tickets, and Activity logs.
5. Standardize chart widgets behind a `VestraChartWidget` abstraction.
6. Apply one set of form section/grid defaults via panel defaults or a form concern.
7. Remove or implement all placeholder table actions before v3.0 launch.
8. Document the component library in `docs/design-system/FILAMENT_COMPONENTS.md`.
