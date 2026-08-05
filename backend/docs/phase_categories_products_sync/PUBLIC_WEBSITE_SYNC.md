# Public Website Synchronization

Category and Product changes made in the Admin Portal propagate to the public Next.js website without manual cache clearing.

## Backend — `App\Services\Catalog\CatalogSyncService`
Called from admin create/update/delete and from the public `CategoryService`/`ProductService` write paths.

Responsibilities:
1. **Forget shared caches** — `catalog.categories.active`, `catalog.products.featured[.N]`, `admin.products.low_stock_count`.
2. **Notify the frontend** — POSTs to `services.frontend.revalidate_url` with header `x-revalidate-secret`, sending `{ type, paths, tags, ids }`. Failures are logged, never fatal (guarded by config presence + try/catch + 3s timeout).

Config added in `config/services.php`:

```php
'frontend' => [
    'revalidate_url' => env('FRONTEND_REVALIDATE_URL'),
    'revalidate_secret' => env('FRONTEND_REVALIDATE_SECRET'),
    'public_url' => env('FRONTEND_PUBLIC_URL', env('NEXT_PUBLIC_SITE_URL', 'http://localhost:3000')),
],
```

## Caching layer
- `CategoryRepository::allActive()` now caches active categories (60s) and forgets on create/update/delete.
- `ProductRepository::getFeatured()` caches featured products (60s) and is invalidated by `CatalogSyncService`.
- Active product queries (`ProductRepository`, `DatabaseSearchProvider`) now require an **active category**, so deactivating a category immediately hides its products from the storefront.

## Frontend — `app/api/revalidate/route.ts`
Next.js route handler that validates `x-revalidate-secret` against `REVALIDATE_SECRET`, then calls `revalidatePath()` for each path and `revalidateTag()` for each tag. Returns `{ revalidated: true, paths, tags }`.

React Query hooks (`use-categories`, `use-products`) use `staleTime: 0` + `refetchOnWindowFocus` so client navigations pick up fresh catalog data.

## Environment
Set on both sides to enable push revalidation:
- Backend: `FRONTEND_REVALIDATE_URL=https://<site>/api/revalidate`, `FRONTEND_REVALIDATE_SECRET=<shared>`
- Frontend: `REVALIDATE_SECRET=<shared>`

If unset, the backend silently skips the push and relies on short-TTL cache expiry + client refetch.
