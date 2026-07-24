# RC1 — Regression Test Report

## Objective

Confirm that all completed stages (18.1–18.5, 18.4A, 18.4A.1) still function correctly after the RC1 deployment.

## Customer Website Regression

| Test Case | URL / Action | Expected Result | Status |
|-----------|--------------|-----------------|--------|
| Homepage loads | `https://vestradetergents.com/` | 200, no console errors | |
| Navigation visible | Homepage | Products, Distributor, Bulk Orders, Account links | |
| Products list | `/products` | Product cards render | |
| Product detail | `/products/{slug}` | Gallery, price, Add to Cart, Buy Now | |
| Search | Search input | Results returned | |
| Add to Cart | Product page | Cart badge updates, toast shown | |
| Cart page | `/cart` | Items, quantities, totals displayed | |
| Checkout | `/checkout` | Address, shipping, summary | |
| Order confirmation | `/checkout/confirm` | Order number, payment required | |
| Customer orders | `/account/orders` | Order list | |
| Order detail | `/account/orders/{id}` | Items, totals, timeline | |
| Order tracking | `/track` or `/account/orders/{id}` | Current status, timeline | |
| Invoice download | Order detail | PDF generated | |
| Customer dashboard | `/account` | Recent orders, quick actions | |
| Mobile layout | Responsive viewport | No layout breakage | |

## API Regression

| Endpoint | Method | Expected | Status |
|----------|--------|----------|--------|
| `/api/v1/health` | GET | 200 OK | |
| `/api/v1/products` | GET | 200, product list | |
| `/api/v1/products/{slug}` | GET | 200, product detail | |
| `/api/v1/cart` | GET | 200, cart contents | |
| `/api/v1/cart` | POST | 200, item added | |
| `/api/v1/auth/login` | POST | 200, token issued | |
| `/api/v1/customer/orders` | GET | 200, orders list | |
| `/api/v1/customer/orders/{id}` | GET | 200, order detail | |
| `/api/v1/payments/initiate` | POST | 200, payment link | |
| `/api/v1/payments/verify/{ref}` | GET | 200, payment status | |

## Admin Portal Regression

| Test Case | URL / Action | Expected Result | Status |
|-----------|--------------|-----------------|--------|
| Admin login | `https://admin.vestradetergents.com/login` | Login page loads | |
| Dashboard | After login | Dashboard renders | |
| Products resource | `/products` | List and edit | |
| Categories resource | `/categories` | List and edit | |
| Orders resource | `/orders` | List and edit | |
| Customers resource | `/customers` | List and edit | |
| Reports pages | `/reports/*` | Charts/tables load | |
| Payments page | `/payments` | Payment records | |
| Settings | `/settings/*` | Configuration loads | |
| Global search | Search bar | Results returned | |
| Livewire assets | Page interactions | No 404 on `/livewire/` | |
| Logout | Logout button | Redirected to login | |

## Legacy Admin Redirects

| Legacy URL | Expected Redirect | Status |
|------------|-------------------|--------|
| `https://api.vestradetergents.com/admin` | `https://admin.vestradetergents.com` | |
| `https://api.vestradetergents.com/admin/login` | `https://admin.vestradetergents.com/login` | |
| `https://api.vestradetergents.com/admin/orders` | `https://admin.vestradetergents.com/orders` | |
| `https://api.vestradetergents.com/admin/products` | `https://admin.vestradetergents.com/products` | |
| `https://api.vestradetergents.com/admin/customers` | `https://admin.vestradetergents.com/customers` | |

## Issues

| Issue | Severity | Steps to Reproduce | Resolution |
|-------|----------|--------------------|------------|
| | | | |

## Conclusion

- [ ] Customer website regression passed.
- [ ] API regression passed.
- [ ] Admin portal regression passed.
- [ ] Legacy redirects passed.
