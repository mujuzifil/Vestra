# Phase 7 — Distributor Directory Architecture

## Database

### Existing Tables
- `distributors` — authorised distributor accounts.
- `distributor_branches` — branch locations with GPS coordinates.

### New Table
- `distributor_service_areas` — links distributors/branches to regions/districts with coverage status.
  - `distributor_id` (required)
  - `branch_id` (optional)
  - `region`, `district`, `status` (covered / coming_soon / planned)

## Backend API

### Endpoints
- `GET /api/v1/public/distributors` — list active distributors with default branch and service areas.
  - Query params: `search`, `district`, `region`
- `GET /api/v1/public/distributors/stats` — network counts.
- `GET /api/v1/public/distributors/coverage` — aggregated coverage by region/district.

### Controller
- `App\Http\Controllers\Api\V1\PublicDistributorController`

### Resources
- `PublicDistributorResource`
- `PublicDistributorBranchResource`

### Settings
- `network_districts_served`
- `network_authorised_partners`
- `network_commercial_customers`
- `network_growing_network_label`

## Frontend

### API Client
- `frontend/lib/api/public-distributors.ts`

### Components
- `frontend/components/distributor/directory-list.tsx`
- `frontend/components/distributor/coverage-map.tsx`

### Page Sections
- `frontend/app/where-to-buy/_components/*.tsx`

## Future Dealer Portal
- The `distributor_service_areas` table provides the foundation for service-area management.
- GPS fields on `distributor_branches` enable future interactive map rendering.
- `operating_hours_json` supports structured opening hours.
