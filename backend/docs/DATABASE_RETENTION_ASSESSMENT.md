# VESTRA Database Retention Assessment

**Scope:** Determine whether `carts` and `cart_items` tables can be safely removed after the public storefront B2B restructure.  
**Status:** Tables remain in place. Removal is blocked by existing order-creation services.

---

## 1. Tables Under Review

| Table | Migration | Current Usage |
|-------|-----------|---------------|
| `carts` | `2026_07_17_000002_create_carts_table.php` | Stores user shopping carts |
| `cart_items` | `2026_07_17_000003_create_cart_items_table.php` | Stores items within each cart |

---

## 2. Reachability from Public Site

**Public frontend no longer reaches these tables.**

Verification:
- `frontend/lib/api/cart.ts` — deleted.
- `frontend/lib/cart-context.tsx` — deleted.
- No frontend imports reference cart APIs.
- All `/cart` and `/checkout` URLs redirect to `/request-quote`.

However, the tables are still actively used by backend order-creation logic.

---

## 3. Dependency Search Results

### 3.1 Direct model usage

```bash
grep -R "Cart::\|CartItem::\|->cart()\|->cart\b" backend/app --include='*.php'
```

Results:
- `backend/app/Models/Cart.php` — internal model relationships (expected)
- `backend/app/Models/CartItem.php` — internal model relationships (expected)
- `backend/app/Models/User.php:113` — `cart()` relationship
- `backend/app/Policies/CartItemPolicy.php` — cart ownership checks
- `backend/app/Providers/AuthServiceProvider.php` — policy registration
- `backend/app/Repositories/CartRepository.php` — cart CRUD
- `backend/app/Services/CartService.php` — cart business logic
- `backend/app/Services/OrderService.php:29` — **order creation reads user cart**
- `backend/app/Services/DistributorOrderService.php:29` — **distributor order creation reads user cart**

### 3.2 Table-name references

```bash
grep -R "carts\|cart_items" backend/app --include='*.php'
```

No direct table-name references outside Cart/CartItem model files, repository, service, and policies.

### 3.3 Controller / service / repository references

```bash
grep -R "CartController\|CheckoutController\|CartService\|CartRepository\|CartResource\|CartItemResource" backend/app --include='*.php'
```

- `CartController` and `CheckoutController` referenced only in `routes/api.php`.
- `CartService`, `CartRepository`, `CartResource`, `CartItemResource` referenced by cart/checkout controllers only.

### 3.4 Filament / report / scheduled command checks

```bash
grep -R "Cart::\|CartItem::\|cart()\|cart_items\|carts" backend/app/Filament backend/app/Console --include='*.php'
```

No references found in Filament resources or scheduled commands.

---

## 4. Critical Blockers to Removal

### 4.1 `OrderService::createFromCheckout()` depends on cart

File: `backend/app/Services/OrderService.php`

```php
$cart = $user->cart?->load('items.product.images');
if (! $cart || $cart->items->isEmpty()) {
    throw ValidationException::withMessages([
        'cart' => ['Your cart is empty.'],
    ]);
}
// ... builds order items from cart items
$cart->items()->delete();
```

This method is invoked by `CheckoutController::store()`. If the public checkout is permanently retired, this code path becomes dead and can be removed alongside the cart tables. If any internal/admin order creation still calls `OrderService::createFromCheckout()`, it must be refactored first.

### 4.2 `DistributorOrderService::createFromCheckout()` depends on cart

File: `backend/app/Services/DistributorOrderService.php`

Mirrors the retail `OrderService` logic. It builds distributor orders from the user's cart. If distributor checkout is retired in favor of quotation-to-order conversion, this path can be removed. Otherwise it must be refactored to accept item arrays directly.

---

## 5. Recommended Refactor Before Dropping Tables

Before `carts` / `cart_items` can be dropped:

1. Confirm `CheckoutController` is removed and `POST /api/v1/checkout` is no longer served.
2. Decide whether `OrderService::createFromCheckout()` and `DistributorOrderService::createFromCheckout()` are still needed.
   - If **yes**: refactor them to accept an items array parameter instead of reading from the cart.
   - If **no**: delete these methods and any callers.
3. Remove `User::cart()` relationship.
4. Remove `CartPolicy`, `CartItemPolicy`, and `AuthServiceProvider` mappings.
5. Remove `CartService`, `CartRepository`, `CartResource`, `CartItemResource`.
6. Only then create a migration to drop `carts` and `cart_items`.

---

## 6. Conclusion

- `carts` and `cart_items` are **not reachable from the public website**.
- They are **still used by backend order services** (`OrderService`, `DistributorOrderService`) via the `User::cart()` relationship.
- **Do not drop these tables yet.** Removal is safe only after the checkout-dependent order-creation methods are refactored or removed.
- All other checked systems (Filament, reports, scheduled commands, notification templates) show no dependency on cart tables.

---

## 7. Sign-Off Checklist

- [ ] `POST /api/v1/checkout` endpoint removed
- [ ] `OrderService::createFromCheckout()` refactored or removed
- [ ] `DistributorOrderService::createFromCheckout()` refactored or removed
- [ ] `User::cart()` relationship removed
- [ ] All cart policies, resources, and services removed
- [ ] Full test suite passes
- [ ] Database backup taken before drop-table migration
