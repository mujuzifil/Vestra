# Frontend — Categories & Products Synchronization

## Changes
1. **`app/api/revalidate/route.ts`** (new) — authenticated on-demand revalidation endpoint. Validates `x-revalidate-secret` against `REVALIDATE_SECRET`, then `revalidatePath()` / `revalidateTag()` for the paths/tags sent by the Laravel `CatalogSyncService`.
2. **`hooks/use-categories.ts`** — `staleTime: 0` + `refetchOnWindowFocus` so category navigation/menus reflect admin changes promptly.
3. **`hooks/use-products.ts`** — same freshness options for product list, search, and detail queries.

## Data flow
Admin saves a Category/Product → `CatalogSyncService` forgets shared Laravel caches and POSTs to `/api/revalidate` → Next.js revalidates `/products` and `/` (and tags `products`, `categories`) → public pages serve fresh data on next request; client views refetch on focus.

## Environment
- `REVALIDATE_SECRET` must match the backend `FRONTEND_REVALIDATE_SECRET`.

## Validation
`npm run lint`, `npx tsc --noEmit`, and `npm run build` all pass.
