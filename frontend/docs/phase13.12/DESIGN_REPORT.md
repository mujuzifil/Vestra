# Phase 13.12 — Design Report

## Visual Reference

The Territories workspace follows the existing Workspace Design System used by Dashboard, Tasks, Notifications, Activity, Companies and Quotes. The primary reference is `Territories.png` in the project root.

## Layout

- Full-width CRM workspace with a custom page header, including a Table/Map segmented view toggle
- Five KPI metric cards at the top (Total Branches, Active, Inactive, Distinct Distributors, Distinct Countries)
- Filter bar with quick multi-select dropdowns (country, district, distributor) plus a collapsible filter panel (adds status)
- **Table view**: enterprise data table with sortable headers, avatar-style branch initials, coordinate-status badges and service-area counts
- **Map view**: a bounded, grid-lined canvas that plots branches by their real latitude/longitude as a proportional scatter, with a status-coloured pin, hover tooltip and legend
- **Map empty state**: a dashed placeholder canvas with a map icon and a clear explanation whenever no in-view branch has both coordinates
- Right-hand detail drawer for branch, parent distributor and service-area context

## Design Tokens

- Colours, spacing, radius, typography and elevation from `backend/resources/css/filament/admin/theme.css`
- KPI cards reuse `x-admin.kpi-card`
- Badges use semantic colour variants: success (active/geocoded), gray (inactive/no coordinates), info

## Why a Coordinate Plot Instead of a Tiled Map

The admin panel has no Leaflet/Mapbox/Google Maps dependency or API key configured. Introducing one would add a new external service dependency, a network requirement and licensing/API-key management out of scope for this phase. Instead, the map view renders pins at positions directly proportional to each branch's real coordinates within the bounding box of the currently visible branches — every pin is traceable to genuine data, and the exact latitude/longitude is always visible in the tooltip and detail drawer.
