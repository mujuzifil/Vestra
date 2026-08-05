# Filter Simplification

## Problem

**Assigned To** remained in the Applications Workspace after it was removed from the approved workflow.

## Removed

| Layer | Change |
|-------|--------|
| `ApplicationsPage` | `$assignedToFilter`, URL param, reset/updated hooks, export query |
| `filter-bar.blade.php` | Assigned To dropdown |
| `application-table` / `application-row` | Assigned To column and assignee cell |
| `ApplicationAdminService` | Eager load, filter, sort, export, filter options assignees |
| `ApplicationExportController` | Filter input and export column |
| Detail drawer | Assigned Administrator section |

## Retained Filters

Status, Priority, Country, Region, Submitted date range, plus header search.
