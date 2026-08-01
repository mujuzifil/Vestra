# VESTRA Public Site — E-Commerce Cleanup Audit

**Scope:** Identify remaining shopping-cart, checkout, and commerce artefacts after Phase 1 B2B restructure.  
**Generated:** 2026-08-01  
**Status:** Cart/checkout UI removed from the public site; backend cart subsystem still registered but unreachable from the new frontend.

---

## 1. Remaining "Buy Now" Buttons

**Result: NONE**

```bash
grep -R "Buy Now\|Buy now\|buy now" frontend
# no matches
```

All `Buy Now` CTAs were replaced with `Request a Quote` / `Contact Sales`.

---

## 2. Remaining "Add to Cart" Buttons

**Result: NONE**

```bash
grep -R "Add to Cart\|Add to cart\|add to cart" frontend
# no matches
```

All `Add to Cart` buttons were removed or replaced with `Request a Quote`.

---

## 3. Remaining "Checkout" Links

**Frontend UI result: NONE**

`checkout` now only appears in infrastructure/SEO files that intentionally handle the obsolete URL:

| File | Line | Context | Action Required |
|------|------|---------|-----------------|
| `frontend/app/robots.ts` | 14, 25 | Disallows `/checkout` | Keep — correct SEO handling |
| `frontend/next.config.ts` | 98–99 | Redirects `/checkout` → `/request-quote` | Keep — preserves bookmarks |

No clickable `Checkout` links remain in any page or component.

---

## 4. Remaining Cart API Calls

**Frontend result: NONE**

All frontend calls to `/cart`, `/cart/items`, `/cart/merge`, and `/checkout` were removed:

- `frontend/lib/api/cart.ts` — deleted
- `frontend/lib/cart-context.tsx` — deleted
- `frontend/hooks/use-orders.ts` — `useCheckout` hook removed
- `frontend/lib/api/orders.ts` — `checkout()` function removed

Backend routes **still registered** and therefore reachable if called directly:

| Method | Route | Controller | Status |
|--------|-------|------------|--------|
| GET | `/api/v1/cart` | `CartController@index` | Orphaned |
| POST | `/api/v1/cart/items` | `CartController@store` | Orphaned |
| PUT | `/api/v1/cart/items/{item}` | `CartController@update` | Orphaned |
| DELETE | `/api/v1/cart/items/{item}` | `CartController@destroy` | Orphaned |
| DELETE | `/api/v1/cart` | `CartController@clear` | Orphaned |
| POST | `/api/v1/cart/merge` | `CartController@merge` | Orphaned |
| POST | `/api/v1/checkout` | `CheckoutController@store` | Orphaned |
| POST | `/api/v1/auth/wishlist/{product}/move-to-cart` | `WishlistController@moveToCart` | Orphaned |
| POST | `/api/v1/auth/saved-for-later/{product}/move-to-cart` | `SavedItemController@moveToCart` | Orphaned |

These endpoints should be removed (or disabled behind admin-only gates) once order-placement flow is fully replaced by quotation requests.

---

## 5. Remaining Cart Icons

**Functional cart icons: NONE**

No icon opens or toggles a cart drawer, mini-cart, or cart page.  Visual icons that remain are generic order/product glyphs reused for B2B purposes:

| File | Line | Icon | Usage |
|------|------|------|-------|
| `frontend/components/distributor/distributor-sidebar.tsx` | 42 | `ShoppingCart` | Distributor **Orders** menu icon |
| `frontend/components/layout/customer-layout.tsx` | 36 | `ShoppingBag` | Customer **Orders** menu icon |
| `frontend/app/account/account-page-client.tsx` | 181 | `ShoppingBag` | **View Products** quick action |
| `frontend/app/account/orders/orders-page-client.tsx` | 313 | `ShoppingBag` | Empty-state **View Products** button |
| `frontend/app/account/orders/[id]/order-detail-client.tsx` | 123 | `heroicon-o-shopping-cart` | Timeline icon |
| `frontend/app/account/recently-viewed/...` | 91 | `ShoppingBag` | Empty-state **View Products** button |
| `frontend/app/account/reviews/reviews-page-client.tsx` | 39, 221 | `ShoppingBag` | Product fallback / empty-state icon |
| `frontend/app/distributor/dashboard/...` | 142–143 | `ShoppingCart` | Order stat cards |
| `frontend/app/distributor/analytics/...` | 60 | `ShoppingCart` | Order stat card |
| `frontend/app/distributor/orders/[id]/...` | 59 | `heroicon-o-shopping-cart` | Timeline icon |

**Recommendation:** These are acceptable; they communicate "orders/products", not a shopping cart.

---

## 6. Shopping-Related Text

### Removed from public pages
- `Continue Shopping` → `View Products`
- `Start Shopping` → `View Products`
- `Move items from your cart…` → `Save products here to review or request a quote later.`
- `Add a delivery address for faster checkout.` → `Add a delivery address for faster order processing.`
- `Join VESTRA for a better shopping experience` → `Join VESTRA for a better business experience`
- `Create a VESTRA account to shop and track orders.` → `Create a VESTRA account to request quotes and track orders.`

### Remaining acceptable occurrences
- `Where to Buy` — new primary navigation item
- `Where can I buy VESTRA products?` — FAQ answer that redirects to distributor/institutional channels
- `buy` inside business-oriented copy (`competitive pricing for bulk buyers`, etc.)

### ⚠️ Placeholder form issue
`frontend/app/request-quote/request-quote-page-client.tsx` renders a quote form but currently **does not submit to any API** (it only toggles a local `submitted` state).  A backend endpoint and form handler must be wired in a future phase.

---

## 7. Dead Routes

### Frontend routes that no longer exist (redirects handle them)
- `/cart` → `/request-quote`
- `/cart/*` → `/request-quote`
- `/checkout` → `/request-quote`
- `/checkout/*` → `/request-quote`
- `/compare` → `/products`
- `/bulk-orders` → `/request-quote`
- `/track` — deleted entirely (no redirect currently configured)

### No internal links to dead routes
```bash
grep -R "href='/cart\|href='/checkout\|href='/compare\|href='/bulk-orders" frontend
# no matches
```

### Backend routes unreachable from the new public site
All `/api/v1/cart/*` and `/api/v1/checkout` routes listed in section 4 are dead from the storefront perspective.  They are still served by Laravel.

---

## 8. Dead Components

### Deleted
- `frontend/app/cart/*`
- `frontend/app/checkout/*`
- `frontend/app/compare/*`
- `frontend/app/bulk-orders/*`
- `frontend/app/track/*`
- `frontend/components/cart/*`
- `frontend/components/checkout/*`
- `frontend/lib/cart-context.tsx`
- `frontend/lib/compare-context.tsx`
- `frontend/lib/api/cart.ts`

### Confirmed no remaining references
```bash
grep -R "from.*cart-context\|from.*compare-context\|@/components/cart\|@/components/checkout\|@/app/cart\|@/app/checkout" frontend
# no matches
```

A full automated scan for every unused exported component was attempted but timed out; manual spot checks of the account and distributor modules found no commerce-only dead components.

---

## 9. Orphaned API Endpoints

Endpoints that exist in `backend/routes/api.php` but are **no longer called by the new frontend**:

| Endpoint | Reason |
|----------|--------|
| `GET /api/v1/cart` | Cart UI removed |
| `POST /api/v1/cart/items` | Cart UI removed |
| `PUT /api/v1/cart/items/{item}` | Cart UI removed |
| `DELETE /api/v1/cart/items/{item}` | Cart UI removed |
| `DELETE /api/v1/cart` | Cart UI removed |
| `POST /api/v1/cart/merge` | Cart UI removed |
| `POST /api/v1/checkout` | Checkout UI removed |
| `POST /api/v1/auth/wishlist/{product}/move-to-cart` | Move-to-cart action removed |
| `POST /api/v1/auth/saved-for-later/{product}/move-to-cart` | Move-to-cart action removed |

Endpoints that are intentionally admin/distributor-only and remain correct:
- `/api/v1/reports/*`
- `/api/v1/admin/*`
- `/api/v1/distributor/*`
- `POST /api/v1/payments/callback` (webhook)

---

## 10. Database Tables Now Unreachable from the Public Site

**Do not remove yet — document only.**

### Fully unreachable from public site
| Table | Migration | Why |
|-------|-----------|-----|
| `carts` | `2026_07_17_000002_create_carts_table.php` | No public cart UI or API call |
| `cart_items` | `2026_07_17_000003_create_cart_items_table.php` | No public cart UI or API call |

### Still reachable from public site
- `categories`, `products`, `product_images`, `media`
- `contact_messages`, `distributor_requests`, `customer_feedback`
- `users`, `customer_addresses`, `customer_preferences`, `customer_deletion_requests`
- `orders`, `order_items`, `order_status_history`, `payment_transactions`, `payment_uploads`
- `wishlists`, `saved_items`, `recently_viewed_products`
- `reviews` and review-related tables
- `notifications`, `notification_templates`, `notification_deliveries`, `announcements`
- `settings`

### Used only by admin / distributor portal (not public site)
These are intentionally retained for back-office operations:
- `quotation_requests`, `quotation_items`
- `distributors`, `distributor_branches`, `distributor_contacts`, `distributor_documents`, `distributor_price_tiers`, `distributor_product_prices`
- `credit_accounts`, `credit_transactions`
- `purchase_orders`, `purchase_order_items`, `suppliers`
- `warehouses`, `product_warehouse_stock`, `stock_movements`
- `customer_notes`, `customer_tags`
- `automated_workflows`
- `audit_logs`, `admin_sessions`, `login_activities`, `exchange_tokens`, `api_request_logs`, `report_snapshots`

---

## 11. Recommendations

1. **Remove orphaned backend routes** once the new quotation/order flow is stable:
   - Delete cart and checkout route groups from `backend/routes/api.php`.
   - Remove `CartController`, `CheckoutController`, `CartService`, `CartRepository`, `CartPolicy`, `CartItemPolicy`, and related Form Requests/Resources.
   - Remove `moveToCart()` methods from `WishlistController` and `SavedItemController`.

2. **Wire the Request a Quote form**:
   - Create a backend endpoint (e.g., `POST /api/v1/quotation-requests`).
   - Update `frontend/app/request-quote/request-quote-page-client.tsx` to actually POST the form.

3. **Drop cart tables** only after:
   - All cart backend code is removed.
   - Data retention policy is confirmed (cart data has no business value once checkout is gone).

4. **Add a redirect for `/track`**:
   - `/track` was deleted but not redirected.  Redirect to `/account/orders` or `/request-quote`.

5. **Run a full dead-component scan** with a faster tool (e.g., `knip` or custom script) to catch any unused components introduced during the refactor.

---

## 12. Summary

| Category | Count | Notes |
|----------|-------|-------|
| "Buy Now" buttons | 0 | Fully removed |
| "Add to Cart" buttons | 0 | Fully removed |
| Checkout links | 0 in UI | Only redirects/robots remain |
| Cart API calls from frontend | 0 | Cart API file deleted |
| Functional cart icons | 0 | Only generic order icons remain |
| Shopping text on public pages | 0 | FAQ "buy" references are B2B-appropriate |
| Dead frontend routes | 6 | Handled by redirects except `/track` |
| Dead frontend components | All cart/checkout/compare removed | No remaining references |
| Orphaned backend API endpoints | 9 | Cart/checkout/move-to-cart routes |
| Unreachable public DB tables | 2 | `carts`, `cart_items` |
