# VESTRA Website — Products Page Runtime Failure Hotfix Report

## 1. Root Cause

The Products page rendered its error state (`ApiError` — "Failed to load products. Please try again.") because every API request from the browser was blocked by the frontend's **Content Security Policy (CSP)**.

The CSP in `frontend/next.config.ts` declared:

```text
connect-src 'self';
```

The frontend runs on `http://localhost:3000` while the Laravel API runs on `http://localhost:8000`. Because the API origin differs from the page origin, the browser refused to connect to `http://localhost:8000/api/v1/*`, causing `fetch` to throw `TypeError: Failed to fetch`. React Query surfaced this as an error and the page displayed the generic failure message.

A secondary defect was discovered during regression testing: `frontend/app/products/[slug]/product-page-client.tsx` called `useProductReviews()` and `useSubmitReview()` **after** early-return statements for loading/error states, violating the Rules of Hooks. This produced:

```text
Rendered more hooks than during the previous render.
```

## 2. Investigation Performed

1. **Backend verification**
   - Confirmed Laravel container is running and database is connected.
   - `GET /api/v1/products` returned `200 OK` with 6 products.
   - `GET /api/v1/categories` returned `200 OK` with 4 categories.
   - `GET /api/v1/products/{slug}` returned `200 OK` for tested slugs.

2. **Connectivity verification**
   - CORS preflight and actual requests from `Origin: http://localhost:3000` succeeded via `curl`.
   - Node.js `fetch` simulation of the exact frontend request succeeded.
   - Environment variable `NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1` was confirmed correct.

3. **Browser runtime investigation**
   - Added temporary diagnostic logging to `frontend/lib/api/client.ts` and `frontend/app/products/page.tsx`.
   - Captured browser console logs with headless Chrome.
   - Console showed repeated CSP violations:

     ```text
     Connecting to 'http://localhost:8000/api/v1/products' violates the following Content Security Policy directive: "connect-src 'self'". The action has been blocked.
     ```

4. **Regression testing after CSP fix**
   - Products page loaded 6 product cards.
   - Search returned filtered results.
   - Category filter functioned correctly.
   - Product detail page opened, but revealed the React hooks-order error above.
   - Moved hooks to the top of `ProductPageClient`; revalidation passed with zero console errors.

## 3. Files Modified

| File | Change |
|------|--------|
| `frontend/next.config.ts` | Added `getApiOrigin()` helper; injected the parsed API origin into `connect-src` and `img-src` CSP directives so cross-origin API calls and product images from Laravel storage are permitted. |
| `frontend/app/products/[slug]/product-page-client.tsx` | Moved `useProductReviews()` and `useSubmitReview()` to the top of the component, before all early-return branches, to comply with the Rules of Hooks. |

Temporary diagnostic changes to `frontend/lib/api/client.ts` and `frontend/app/products/page.tsx` were added and then fully reverted.

## 4. API Validation

| Endpoint | Method | Status | Notes |
|----------|--------|--------|-------|
| `/api/v1/products` | GET | 200 OK | Returns 6 products |
| `/api/v1/categories` | GET | 200 OK | Returns 4 categories |
| `/api/v1/settings` | GET | 200 OK | Returns CMS settings |
| `/api/v1/products/{slug}` | GET | 200 OK | Returns product detail |
| `/api/v1/products/{slug}/reviews` | GET | 200 OK | Returns reviews data |

## 5. Frontend Validation

- `/products` loads without the "Failed to load products" message.
- 6 product cards render.
- Product images load from `http://localhost:8000/storage/**`.
- Search box filters products by name/description/category.
- Category filter buttons filter the grid.
- Product detail page (`/products/{slug}`) opens and renders correctly.
- No browser console errors remain after the hooks fix.

## 6. Backend Validation

- Laravel dev server responding on `http://localhost:8000`.
- Database connected and seeded.
- No SQL errors or exceptions in the API responses.

## 7. Runtime Validation

- Frontend dev server restarted after CSP configuration change.
- Frontend dev server restarted after hooks fix.
- Browser cache and Next.js cache cleared between runs.
- All pages verified in a headless browser with network and console capture enabled.

## 8. Regression Results

| Check | Result |
|-------|--------|
| Products page loads | ✅ PASS |
| Product cards render | ✅ PASS |
| Images display | ✅ PASS |
| Search works | ✅ PASS |
| Category filtering works | ✅ PASS |
| Product detail page opens | ✅ PASS |
| Browser refresh works | ✅ PASS |
| No console errors | ✅ PASS |

## 9. Build Results

```bash
cd frontend && npm run build
```

- Status: **Success**
- Warnings: minor unused-import/eslint warnings only; no build or type errors.

## 10. Before vs After

| Aspect | Before Hotfix | After Hotfix |
|--------|---------------|--------------|
| `/products` product grid | Error: "Failed to load products. Please try again." | 6 product cards render |
| API requests in browser | Blocked by CSP `connect-src 'self'` | Allowed to `http://localhost:8000` |
| Product detail page | Hooks-order crash on data load | Renders cleanly |
| Console errors | CSP violations + hook error | None observed |

## 11. Final Status

**PASS**

The Products page runtime failure is resolved. The product catalog now loads live data from the Laravel API, search and filtering work, product detail pages open without errors, and the frontend production build succeeds.
