# Stage 24.13B — Homepage Conversion CTAs & Distributor Locator Enhancement

**Status:** Implemented — releasing (commit / merge / deploy)  
**Gate (from 24.13A):** READY WITH CONDITIONS  
**Implementation branch:** `feature/stage24-13b-homepage-locator`  
**Date:** 2026-08-09

---

## Production baseline (pre-change)

Confirmed before implementation (do **not** use historical tip `a9e9c96` / image `local-20260806140735`):

| Item | Value |
|------|--------|
| Production / repo tip at start | `5443b07` (`5443b07d6ba1cd161c38d33a3ed64da12b4e680a`) |
| Production version (prior audits) | `v2.1.0-31-g5443b07` |
| Production image (prior audits) | `local-20260807093405` |
| Branch base | `master` @ `5443b07` |

---

## Objectives completed

### A. Homepage conversion CTA

- Extended existing hero CTA row in `frontend/components/sections/hero-section.tsx`.
- CTAs (unchanged Quote + Distributor; added Where to Buy):
  1. Request a Quote → `/request-quote` (`hero-primary-cta`)
  2. Become a Distributor → `/distributor` (`hero-secondary-cta`)
  3. Where to Buy → `/where-to-buy` (`hero-where-to-buy-cta`)
- Navigation items were **not** modified or duplicated.

### B. Distributor locator enhancement (additive)

- Reused `/where-to-buy`, `DirectoryList`, and public distributor APIs.
- Did **not** fabricate ACTIVE distributors; empty directory remains valid when `data: []`.

---

## Data model

Migration: `2026_08_09_120000_add_locator_fields_to_distributors_table.php`

| Field | Type / enum |
|-------|-------------|
| `tier` | `silver` \| `gold` \| `master` (default `silver`) |
| `whatsapp` | nullable string |
| `google_maps_url` | nullable string |
| `stock_availability` | `in_stock` \| `low_stock` \| `out_of_stock` (default `in_stock`) |

Enums:

- `App\Enums\DistributorTier`
- `App\Enums\DistributorStockAvailability`

Existing fields reused: `district`, `city` (area/town), `phone`, `operating_hours_json`.

Onboarding approve defaults new partners to Silver + In Stock.

---

## Public API

`GET /api/v1/public/distributors` extended (existing search/district/region preserved):

- New response fields: `tier`, `tier_label`, `whatsapp`, `google_maps_url`, `stock_availability`, `stock_availability_label`, `area` (alias of `city`)
- New filters: `area` / `city`, `tier`, `stock_availability`
- Invalid enum filters → `422`

---

## Admin

- Extended Filament `DistributorResource` form (tier, stock, WhatsApp, Maps URL, district, area/town, hours).
- Registered `create` + `edit` pages; fixed View → Edit action.
- Active Partners detail drawer: Edit link, tier/stock badges, Public Locator section.
- `PartnerAdminService::updateProfile` allow-list includes new locator fields.

---

## Public Where to Buy UI

- Hero + directory trust/search messaging for authorized distributors.
- Search: name, district, area/town, region.
- Filters: tier (All/Silver/Gold/Master), stock (All/In/Low/Out).
- Cards: tier badge, stock, location, phone, WhatsApp, hours, Google Maps (per-distributor URL).
- Empty state: “No Authorized VESTRA Distributors found for this location.” + clear filters / contact actions.
- No fake partner records.

---

## Tests

```text
phpunit tests/Feature/Api/PublicDistributorLocatorTest.php
         tests/Feature/Admin/DistributorLifecycleTest.php
         tests/Feature/Admin/ActivePartnersPageTest.php
```

Result: **OK (31 tests, 114 assertions)**

---

## Preserved functionality (non-negotiable)

- All 8 primary nav items, mobile drawer, search, login
- Existing Quote / Distributor / Where to Buy pages and routes
- Existing public distributor API + DirectoryList foundation
- Existing homepage Quote + Distributor CTAs (Where to Buy added only)

---

## Deploy checklist (post-commit)

1. Merge feature branch → `develop` / `master` as per release process.
2. Production deploy via `./scripts/deploy.sh --build` from `/opt/vestra`.
3. Smoke:
   - Homepage hero shows three CTAs; Where to Buy → `/where-to-buy`
   - `GET /api/v1/public/distributors` still succeeds (may be empty)
   - Admin can edit partner tier / WhatsApp / maps / stock without code deploy for content
   - Empty state renders professionally when no ACTIVE partners match
