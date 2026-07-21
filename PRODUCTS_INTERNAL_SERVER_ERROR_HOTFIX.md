# VESTRA Website — Products Page Internal Server Error Hotfix Report

## 1. Root Cause

`GET http://localhost:3000/products` returned **Internal Server Error** because the Next.js Turbopack dev server could not write its development build manifest. The terminal showed repeated `ENOENT` errors:

```text
⨯ [Error: ENOENT: no such file or directory, open 'F:\Vestra website\frontend\.next\static\development\_buildManifest.js.tmp.icy0xqn1fb'] {
  errno: -4058,
  code: 'ENOENT',
  syscall: 'open',
  path: 'F:\\Vestra website\\frontend\\.next\\static\\development\\_buildManifest.js.tmp.icy0xqn1fb'
}
```

The `.next` directory contained mixed artifacts from:
- prior `npm run dev` / Turbopack runs (`static/development`, `cache`)
- the production build executed earlier (`BUILD_ID`, `standalone`, prerender manifests, etc.)

This left the development cache in an inconsistent state. Turbopack attempted to atomically write `_buildManifest.js` using a temporary file, but the parent directory state was invalid, causing SSR to fail for `/products` and other routes.

## 2. Stack Trace

The exception was raised internally by Next.js/Turbopack during server-side rendering. The observable surface was:

```text
Error: ENOENT: no such file or directory, open 'F:\Vestra website\frontend\.next\static\development\_buildManifest.js.tmp.<random>'
    at async open (node:internal/fs/promises:639:25)
    ...
```

No application code appeared in the trace; it was a build-cache/filesystem failure.

## 3. Files Modified

No source-code files were modified. The fix was a runtime/cache remediation.

- Deleted: `frontend/.next` (entire Next.js build/dev cache)
- Created: `PRODUCTS_INTERNAL_SERVER_ERROR_HOTFIX.md`

## 4. Why the Failure Occurred

The failure occurred because the `.next` cache directory had stale/corrupted development artifacts. Running `npm run build` (production) and then repeatedly restarting `npm run dev` (Turbopack) without clearing the cache caused the development build manifest writer to fail.

## 5. Why Previous Testing Did Not Expose It

Earlier in the same session `/products` rendered correctly because the dev cache was still coherent. The cache became corrupted after the production build and subsequent dev-server restarts, which happened between the previous successful test and the current request.

## 6. Fix Implemented

1. Stopped the running frontend dev server.
2. Removed the entire `frontend/.next` directory.
3. Ran `npm install` to refresh dependencies and lockfile state.
4. Restarted `npm run dev` with a clean cache.

## 7. Runtime Validation

- `curl http://localhost:3000/products` now returns **HTTP 200 OK** with the rendered Products page HTML.
- Next.js dev server terminal shows:

  ```text
  GET /products 200 in 7873ms
  GET /products 200 in 649ms
  GET /products/silk-care 200 in 4919ms
  ```

- No `ENOENT` or `_buildManifest.js.tmp` errors after the cache clear.

## 8. Regression Testing

Executed a headless browser regression suite against `http://localhost:3000/products`:

| Check | Result |
|-------|--------|
| Products page loads without "Internal Server Error" | ✅ PASS |
| 6 product cards render | ✅ PASS |
| Search filters products | ✅ PASS |
| Category filter functions | ✅ PASS |
| Product detail page opens | ✅ PASS |
| No browser console errors | ✅ PASS |

API calls observed during the test all returned `200 OK`:
- `GET /api/v1/settings`
- `GET /api/v1/products`
- `GET /api/v1/categories`
- `GET /api/v1/products/silk-care`
- `GET /api/v1/products/silk-care/reviews?page=1`

## 9. Build Validation

```bash
cd frontend && npm run build
```

- Status: **Success**
- 24 static/dynamic pages generated.
- Only pre-existing ESLint warnings remain; no build errors.

## 10. Final Status

**PASS**

`/products` now returns HTTP 200, renders the live product catalogue, and passes all regression checks. No source-code changes were required; the issue was resolved by clearing the corrupted Next.js development cache.
