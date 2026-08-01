# VESTRA Backend Commerce Cleanup Plan

**Status:** Planned — do not execute until Phase 2+ approval.  
**Scope:** Remove the obsolete shopping-cart and checkout subsystem that is no longer reachable from the public storefront after the B2B restructure.

---

## 1. Context

Phase 1 removed all cart/checkout UI from the public Next.js frontend. Phase 1.5 added a public quote-request workflow. The backend still registers cart and checkout endpoints, but no legitimate frontend traffic uses them. This plan documents exactly what must be removed and the dependency checks required before removal.

---

## 2. Routes to Remove

| Method | Route | Controller | Reason |
|--------|-------|------------|--------|
| GET | `/api/v1/cart` | `CartController@index` | Cart UI removed |
| POST | `/api/v1/cart/items` | `CartController@store` | Cart UI removed |
| PUT | `/api/v1/cart/items/{item}` | `CartController@update` | Cart UI removed |
| DELETE | `/api/v1/cart/items/{item}` | `CartController@destroy` | Cart UI removed |
| DELETE | `/api/v1/cart` | `CartController@clear` | Cart UI removed |
| POST | `/api/v1/cart/merge` | `CartController@merge` | Cart UI removed |
| POST | `/api/v1/checkout` | `CheckoutController@store` | Checkout UI removed |
| POST | `/api/v1/auth/wishlist/{product}/move-to-cart` | `WishlistController@moveToCart` | Move-to-cart action removed |
| POST | `/api/v1/auth/saved-for-later/{product}/move-to-cart` | `SavedItemController@moveToCart` | Move-to-cart action removed |

### Route file changes
- `backend/routes/api.php`
  - Remove `CartController` and `CheckoutController` imports.
  - Delete the `// Cart` route group.
  - Delete `Route::post('/checkout', ...)`.
  - Delete move-to-cart routes from wishlist and saved-for-later groups.

---

## 3. Controllers to Remove or Modify

### Delete entirely
- `backend/app/Http/Controllers/Api/V1/CartController.php`
- `backend/app/Http/Controllers/Api/V1/CheckoutController.php`

### Modify
- `backend/app/Http/Controllers/Api/V1/WishlistController.php`
  - Remove `moveToCart()` method.
  - Remove any cart-related imports.
- `backend/app/Http/Controllers/Api/V1/SavedItemController.php`
  - Remove `moveToCart()` method.
  - Remove any cart-related imports.

---

## 4. Services / Repositories / Policies / Resources / Requests

### Delete
| File | Purpose |
|------|---------|
| `backend/app/Services/CartService.php` | Cart business logic |
| `backend/app/Repositories/CartRepository.php` | Cart data access |
| `backend/app/Policies/CartPolicy.php` | Cart authorization |
| `backend/app/Policies/CartItemPolicy.php` | Cart item authorization |
| `backend/app/Http/Resources/V1/CartResource.php` | Cart API response |
| `backend/app/Http/Resources/V1/CartItemResource.php` | Cart item API response |
| `backend/app/Http/Requests/Api/V1/AddToCartRequest.php` | Add-to-cart validation |
| `backend/app/Http/Requests/Api/V1/UpdateCartItemRequest.php` | Update-cart-item validation |
| `backend/app/Http/Requests/Api/V1/CheckoutRequest.php` | Checkout validation |

### Modify
- `backend/app/Providers/AuthServiceProvider.php`
  - Remove `Cart::class => CartPolicy::class` and `CartItem::class => CartItemPolicy::class` mappings.
  - Remove related imports.

---

## 5. Models / Database Tables

### Delete models
- `backend/app/Models/Cart.php`
- `backend/app/Models/CartItem.php`

### Drop tables
- `carts`
- `cart_items`

### Migration removal
- Delete `backend/database/migrations/2026_07_17_000002_create_carts_table.php`
- Delete `backend/database/migrations/2026_07_17_000003_create_cart_items_table.php`
- Create a one-time cleanup migration `drop_carts_and_cart_items_tables` for already-deployed environments if migrations have already run.

### Model relationships
- `backend/app/Models/User.php`
  - Remove `cart()` relationship if confirmed unused.

---

## 6. Frontend Dead Code Already Removed

The following were deleted during Phase 1:

- `frontend/lib/cart-context.tsx`
- `frontend/lib/compare-context.tsx`
- `frontend/lib/api/cart.ts`
- `frontend/components/cart/*`
- `frontend/components/checkout/*`
- `frontend/app/cart/*`
- `frontend/app/checkout/*`
- `frontend/app/compare/*`
- `frontend/app/bulk-orders/*`
- `frontend/app/track/*`

---

## 7. Tests to Remove or Update

### Delete
- `backend/tests/Feature/Api/V1/CartControllerTest.php`
- `backend/tests/Feature/Api/V1/CheckoutTest.php`

### Update
- `backend/tests/Feature/Api/V1/WishlistControllerTest.php`
  - Remove move-to-cart test cases.
- `backend/tests/Feature/Api/V1/SavedItemControllerTest.php`
  - Remove move-to-cart test cases.

---

## 8. Dependency Checks Required Before Removal

Before executing this cleanup, verify NONE of the following depend on the cart subsystem:

1. **Distributor order conversion** — `QuotationService` / `DistributorOrderService` must not call cart methods.
2. **Admin order creation** — Filament `OrderResource` must not create orders via cart.
3. **Reports / analytics** — No report reads `carts` or `cart_items`.
4. **Scheduled jobs** — No command reads cart tables.
5. **Notification templates** — No template references cart data.
6. **User model** — `User::cart()` is not used outside `CartService`.
7. **Third-party integrations** — No payment/webhook code uses cart identifiers.

### Search commands

```bash
# Cart model usage
grep -R "Cart::\|CartItem::\|->cart()\|->cart\b" backend/app --include='*.php'

# Table names
grep -R "carts\|cart_items" backend/app --include='*.php'

# Controller / service / repository references
grep -R "CartController\|CheckoutController\|CartService\|CartRepository\|CartResource\|CartItemResource" backend/app --include='*.php'

# Model relationships
grep -R "function cart()" backend/app --include='*.php'

# Tests
grep -R "CartController\|CheckoutController\|moveToCart\|move_to_cart" backend/tests --include='*.php'
```

All of the above should return no critical matches before dropping tables.

---

## 9. Execution Order

1. Run dependency searches and resolve any unexpected usage.
2. Remove routes.
3. Remove controllers, services, repositories, policies, resources, requests.
4. Remove `User::cart()` relationship if unused.
5. Update `AuthServiceProvider`.
6. Delete cart model files.
7. Remove cart migrations from repository.
8. Create and run drop-table migration for deployed environments.
9. Delete/update tests.
10. Run full test suite.
11. Update documentation.

---

## 10. Risk Assessment

| Risk | Mitigation |
|------|------------|
| Distributor portal breakage | Keep distributor quotation/order flow separate; do not touch `QuotationService` |
| Admin order management breakage | Orders table remains; only cart tables removed |
| Data loss | Cart data has no value once checkout is gone; still take a DB backup before dropping tables |
| Report breakage | Verify reports do not reference cart tables |

---

## 11. Sign-Off

This plan is approved for execution only after:
- Public quote request workflow is live and stable.
- Dependency searches return clean.
- Stakeholder sign-off on dropping cart data.
