# Companies Nav Fix + Restyle — Production Deployment Report

## Summary

Deployed the Companies workspace restyle (Quotes-pattern layout vs `Companies.png`) and fixed Sales → Companies navigation so it no longer opens the legacy Filament Customers list at `/customers`.

## Commit Deployed

- **Branch:** `master`
- **Merge commit:** `94095e0` (`Merge branch 'develop' for Companies nav fix and restyle`)
- **Feature commit:** `5c9f0a1` (`fix(admin): route Companies nav to workspace and restyle vs Companies.png`)
- **Deployment time:** 2026-08-03 20:46–20:51 UTC
- **Image tag:** `local-20260803204610`
- **Rollback target:** `local-20260803192737`

## Changes

- Hide `CustomerResource` from Filament navigation; empty `getNavigationItems()`
- `/customers` list redirects to `/sales/companies`
- Companies page restyled: toolbar search, right filter panel, mockup-aligned columns (Open Quotes / Active Tickets instead of fabricated Revenue/Opportunities)

## Validation

| Check | Result |
|---|---|
| Public site | 200 |
| API health | 200 |
| Admin login | 200 |
| `/sales/companies` | 302 → login (auth enforced) |
| `/customers` | 302 → login (auth enforced; redirects to Companies when authenticated) |
| Containers | All healthy (`local-20260803204610`) |
| Migrations | Nothing to migrate |

## Note

`deploy.sh --build` exited with frontend health-check race (same as prior phases); containers became healthy shortly after. Caches cleared post-deploy.

## Conclusion

Production is live on `94095e0`. Sidebar **Companies** should open `https://admin.vestradetergents.com/sales/companies`. Hard-refresh if the old Customers page is cached.
