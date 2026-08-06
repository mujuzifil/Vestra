# Stage 24.9 — Distributor Lifecycle & Full-Stack Synchronization

## Objective

Establish one canonical distributor record after application approval, with automatic partner, coverage, credit, portal, and public-website synchronization. Also ship the Stage 24.9 admin shell/UI cleanup items.

## Architecture

- `DistributorRequest` = historical application snapshot
- `Distributor` = canonical operational partner after approval
- Coverage via `distributor_service_areas`
- Credit via `credit_accounts` initialized on approval only
- Public directory/coverage APIs read active distributors only

## Key behaviors

1. Approve/reject/review only through `DistributorOnboardingService`
2. Orphan approved applications can be repaired (UI + `distributors:repair-lifecycle`)
3. Suspend removes partner from public discovery without deleting history
4. Territories has no Add Branch workflow
5. Catalog sync pushes `/where-to-buy` revalidation on distributor changes
6. Inventory stock adjustments sync public product caches

## Removed navigation

- Sales: Pipeline, Opportunities
- Products: Inventory page (services retained)
- Operations: Suppliers, Purchase Orders, Workflows

## Validation

- `DistributorLifecycleTest`
- `ApplicationsPageTest`
- `ActivePartnersPageTest`
- `StaffPageTest` / `RolesPageTest` / `ProfilePageTest` / `BlogPageTest`

## Deploy

Merge `feature/stage24-9-distributor-lifecycle` → `develop` → `master`, then:

```bash
cd /opt/vestra
git pull origin master
./scripts/deploy.sh --build
php artisan distributors:repair-lifecycle
```
