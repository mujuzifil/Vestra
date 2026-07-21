# VESTRA Website — Product Details Runtime Error & Media Integrity Hotfix Report

**Severity:** High  
**Date:** 2026-07-19  
**Status:** PASS

---

## 1. Root Cause

Every Product Details page was failing with the generic Next.js error page because the page is a Server Component. It fetches product data server-side via `getProductBySlug()`, which uses the shared API client in `frontend/lib/api/client.ts`.

The API client built request URLs from `NEXT_PUBLIC_API_URL`, which is set to `http://localhost:8000/api/v1`. This works in the browser (where `localhost:8000` resolves to the host), but inside the frontend Docker container `localhost` resolves to the frontend container itself. The backend is not running there, so the server-side `fetch()` failed with `ECONNREFUSED`.

The Products listing page worked because it is a Client Component (`"use client"`) and fetches from the browser.

---

## 2. Runtime Exception

```
TypeError: fetch failed
  [cause]: AggregateError { code: 'ECONNREFUSED' }
```

This was thrown during SSR for `frontend/app/products/[slug]/page.tsx` when it called `getProductBySlug()`.

---

## 3. Stack Trace

The error originated in:

- `frontend/app/products/[slug]/page.tsx` → `generateMetadata()` / `ProductPage()`
- `frontend/lib/api/products.ts` → `getProductBySlug()`
- `frontend/lib/api/client.ts` → `apiGet()` using `API_URL = http://localhost:8000/api/v1`

Inside the Docker container, `localhost:8000` does not resolve to the backend service, causing the connection to be refused.

---

## 4. API Validation

Tested `GET /api/v1/products/{slug}` for every seeded product:

| Product | HTTP Status |
|---------|-------------|
| EcoSuit Cleaner | 200 |
| Heavy Duty Detergent | 200 |
| Silk Care | 200 |
| Stain Pro | 200 |
| Wool & Delicate Fabric Wash | 200 |
| Pro Finish Garment Spray | 200 |

The backend API was healthy; the failure was isolated to the frontend server-side request routing.

---

## 5. Product Validation

All products contain valid:

- slug
- name
- description
- price
- category (with name and slug)
- image path
- alt text

No null or malformed fields were detected in the seeded data.

---

## 6. Reviews Validation

- `GET /api/v1/products/{slug}/reviews` returns HTTP 200 with a paginated collection.
- When no reviews exist, it returns an empty collection with `average_rating = 0` and `review_count = 0`.
- The `ReviewList` component renders an empty-state message when `reviewCount === 0`.
- No runtime errors were observed in the reviews section.

---

## 7. Media Integrity Improvements

To prevent future silent image mismatches, the following permanent validations were introduced:

### Seeder validation
`ProductSeeder.php` now verifies that the expected image file exists in `storage/app/public/products/` before creating the `ProductImage` record. If the file is missing, seeding fails with a clear error message.

### Artisan command
`php artisan media:validate` scans every `ProductImage` record and verifies the physical file exists. It also validates the `public/storage` symlink. It exits with code 0 on success and non-zero on failure.

### CI integration
The `media:validate` command was added to `.github/workflows/ci.yml` so future builds fail if referenced product images are missing.

### Placeholder strategy
The frontend already falls back to `/assets/images/products/placeholder.png` when an image is missing, preventing broken image icons.

---

## 8. Seeder Validation

File: `backend/database/seeders/ProductSeeder.php`

Before inserting a product image, the seeder now runs:

```php
$imagePath = "products/{$product->slug}.png";

if (! Storage::disk('public')->exists($imagePath)) {
    throw new RuntimeException(
        "Product image missing for [{$product->name}]. Expected file: storage/app/public/{$imagePath}"
    );
}
```

This ensures filename mismatches like the previous `pro-finish.png` vs `pro-finish-garment-spray.png` issue cannot silently reach UAT again.

---

## 9. Artisan Validation Command

File: `backend/app/Console/Commands/ValidateMediaCommand.php`

Registered as `media:validate`.

Example output (success):

```
Checking storage symlink...
✔ public/storage symlink is valid.

Checking product images...
✔ EcoSuit Cleaner — products/ecosuit-cleaner.png
✔ Heavy Duty Detergent — products/heavy-duty-detergent.png
✔ Silk Care — products/silk-care.png
✔ Stain Pro — products/stain-pro.png
✔ Wool & Delicate Fabric Wash — products/wool-delicate-fabric-wash.png
✔ Pro Finish Garment Spray — products/pro-finish-garment-spray.png

All product media validated successfully.
```

Example output (failure, when a file is temporarily missing):

```
✖ Pro Finish Garment Spray — products/pro-finish-garment-spray.png
   Missing: /var/www/html/storage/app/public/products/pro-finish-garment-spray.png

Validation failed: 1 issue(s) found.
```

Exit code: `1` on failure.

---

## 10. Files Created

| File | Purpose |
|------|---------|
| `backend/app/Console/Commands/ValidateMediaCommand.php` | Implements `php artisan media:validate` |
| `PRODUCT_DETAILS_RUNTIME_AND_MEDIA_INTEGRITY_REPORT.md` | This report |

---

## 11. Files Modified

| File | Change |
|------|--------|
| `frontend/lib/api/client.ts` | Uses `API_BASE_URL` for server-side fetches and `NEXT_PUBLIC_API_URL` for browser fetches |
| `docker-compose.dev.yml` | Added `API_BASE_URL=http://vestra-backend-dev:8000/api/v1` to the frontend service |
| `backend/database/seeders/ProductSeeder.php` | Added storage existence check before creating product images |
| `.github/workflows/ci.yml` | Added product image copy, seeding, storage link, and `media:validate` steps |
| `frontend/public/assets/images/products/pro-finish.png` | Renamed to `pro-finish-garment-spray.png` for naming consistency |
| `backend/storage/app/public/products/pro-finish.png` | Renamed to `pro-finish-garment-spray.png` (carried over from previous hotfix) |

---

## 12. Regression Results

### Product detail pages

| Product | Page Status |
|---------|-------------|
| `/products/ecosuit-cleaner` | 200 ✅ |
| `/products/heavy-duty-detergent` | 200 ✅ |
| `/products/silk-care` | 200 ✅ |
| `/products/stain-pro` | 200 ✅ |
| `/products/wool-delicate-fabric-wash` | 200 ✅ |
| `/products/pro-finish-garment-spray` | 200 ✅ |

### Other pages

- `/products` listing page — 200 ✅
- `/` home page — 200 ✅
- Featured products section — renders ✅
- Product images — display ✅
- Reviews section — renders empty state ✅

### Commands

- `php artisan media:validate` — exit code 0 ✅
- Temporary file removal test — exit code 1 ✅

---

## 13. Before vs After

### Before

- Clicking **View Details** on any product showed:
  > Something went wrong  
  > An unexpected error occurred while loading this page.
- Server logs showed `fetch failed` / `ECONNREFUSED`.
- Missing product images could silently reach UAT.

### After

- Every Product Details page loads successfully.
- Server-side API calls inside Docker use the internal backend service name.
- `ProductSeeder` fails fast if an expected image file is missing.
- `php artisan media:validate` detects missing or mismatched images.
- CI fails the build when product media is invalid.

---

## 14. Production Readiness Impact

- **Runtime stability:** Product Details pages no longer crash in containerized environments.
- **Media integrity:** Future image filename mismatches are caught during seeding, by command, and in CI.
- **Deploy safety:** The `media:validate` command can be run before or after deployment to verify assets.
- **No breaking changes:** Existing API contracts, database schema, and frontend components are unchanged.

---

## Follow-up: Products Page Loading Verification

During validation it was discovered that the global replace used to introduce `getApiUrl()` accidentally created `process.env.NEXT_PUBLIC_getApiUrl()` in `frontend/lib/api/client.ts`. This was immediately corrected to `process.env.NEXT_PUBLIC_API_URL`, and the frontend container was recreated.

After the correction:

- Browser bundle uses `http://localhost:8000/api/v1` for client-side API calls.
- Server bundle uses `API_BASE_URL=http://vestra-backend-dev:8000/api/v1` for SSR.
- `GET /products` returns 200 and renders the product grid.
- All `GET /products/[slug]` pages return 200.
- Search, category filtering, product images, reviews, and related products all function correctly.

---

## 15. Final Status

**PASS**

- Every Product Details page loads successfully.
- No runtime exceptions occur.
- Reviews load correctly (empty state when none exist).
- Product images load correctly.
- `php artisan media:validate` detects missing assets and exits non-zero on failure.
- `ProductSeeder` fails when an expected image file is missing.
- Placeholder images prevent broken image icons.
- Regression testing confirms all products, images, and detail pages function correctly.
