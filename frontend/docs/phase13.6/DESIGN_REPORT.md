# Phase 13.6 — Design Report

## Visual Reference

The Companies workspace follows the existing Workspace Design System used by Dashboard, Tasks, Notifications and Activity. The primary reference is `Companies.png` in the project root.

## Layout

- Full-width CRM workspace with custom page header
- Five KPI metric cards at the top
- Filter bar with multi-select dropdowns, date range and toggles
- Enterprise data table with sortable headers and row actions
- Right-hand detail drawer for company context, relationships and quick actions
- Right-hand form drawer for create/edit
- Right-hand import drawer for CSV upload

## Design Tokens

- Colours, spacing, radius, typography and elevation from `backend/resources/css/filament/admin/theme.css`
- KPI cards reuse `x-admin.kpi-card`
- Badges use semantic colour variants: primary, success, warning, danger, info, gray
