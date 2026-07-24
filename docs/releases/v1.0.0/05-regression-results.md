# RG1 — Regression Test Results

## Objective

Confirm that all Version 1.0 functionality remains operational after deployment.

## Customer Website

| # | Test Case | URL / Action | Expected Result | Status | Notes |
|---|-----------|--------------|-----------------|--------|-------|
| 1 | Homepage loads | `/` | 200, no console errors | | |
| 2 | Navigation visible | Homepage | Products, Distributor, Bulk Orders, Account | | |
| 3 | Products list | `/products` | Products render with images and prices | | |
| 4 | Product search | Search input | Results update | | |
| 5 | Product detail | `/products/{slug}` | Gallery, SKU, price, stock, Add to Cart, Buy Now | | |
| 6 | Add to Cart | Product page | Badge updates, toast shown | | |
| 7 | Cart drawer | Cart icon | Items, quantities, totals, checkout button | | |
| 8 | Full cart page | `/cart` | Items, quantities, subtotal, remove, continue | | |
| 9 | Checkout | `/checkout` | Customer info, address, shipping, summary | | |
| 10 | Place order | Checkout | Order created, redirect to confirmation | | |
| 11 | Payment initiation | `/checkout/confirm` | Flutterwave checkout opens | | |
| 12 | Customer orders | `/account/orders` | Order list | | |
| 13 | Order detail | `/account/orders/{id}` | Items, totals, timeline, payment status | | |
| 14 | Invoice download | Order detail | PDF generated | | |
| 15 | Order tracking | `/track` or order detail | Timeline and current status | | |
| 16 | Customer dashboard | `/account` | Recent orders, quick actions | | |
| 17 | Distributor application | `/distributor` | Form submits, success page | | |
| 18 | Bulk orders | `/bulk-orders` | Quote request form | | |
| 19 | Mobile responsive | Mobile viewport | Layout usable | | |

## REST API

| # | Endpoint | Method | Expected | Status | Notes |
|---|----------|--------|----------|--------|-------|
| 1 | `/api/v1/health` | GET | 200 OK | | |
| 2 | `/api/v1/products` | GET | 200, product list | | |
| 3 | `/api/v1/products/{slug}` | GET | 200, product detail | | |
| 4 | `/api/v1/cart` | GET | 200, cart | | |
| 5 | `/api/v1/cart` | POST | 200, item added | | |
| 6 | `/api/v1/auth/login` | POST | 200, token | | |
| 7 | `/api/v1/auth/register` | POST | 201, customer created | | |
| 8 | `/api/v1/customer/orders` | GET | 200, orders | | |
| 9 | `/api/v1/customer/orders/{id}` | GET | 200, order detail | | |
| 10 | `/api/v1/payments/initiate` | POST | 200, payment data | | |
| 11 | `/api/v1/payments/verify/{ref}` | GET | 200, status | | |
| 12 | `/api/v1/payments/callback` | POST | 200, webhook handled | | |

## Admin Portal

| # | Test Case | URL / Action | Expected | Status | Notes |
|---|-----------|--------------|----------|--------|-------|
| 1 | Login page | `https://admin.vestradetergents.com/login` | Loads | | |
| 2 | Login | Credentials | Dashboard loads | | |
| 3 | Dashboard | `/` | Widgets render | | |
| 4 | Products list | `/products` | List loads | | |
| 5 | Product edit | `/products/{id}/edit` | Form loads, saves | | |
| 6 | Categories | `/categories` | List loads | | |
| 7 | Orders list | `/orders` | List loads | | |
| 8 | Order edit | `/orders/{id}/edit` | Details and actions load | | |
| 9 | Customers | `/customers` | List loads | | |
| 10 | Reports | `/reports` | Reports render | | |
| 11 | Payments | `/payments` | Payment records | | |
| 12 | Settings | `/settings` | Configuration loads | | |
| 13 | Global search | Search bar | Results returned | | |
| 14 | Livewire assets | Interactions | No 404 on `/livewire/` | | |
| 15 | Logout | Logout button | Redirect to login | | |

## Legacy Redirects

| # | Legacy URL | Expected Target | Status |
|---|------------|-----------------|--------|
| 1 | `https://api.vestradetergents.com/admin` | `https://admin.vestradetergents.com` | |
| 2 | `https://api.vestradetergents.com/admin/login` | `https://admin.vestradetergents.com/login` | |
| 3 | `https://api.vestradetergents.com/admin/orders` | `https://admin.vestradetergents.com/orders` | |
| 4 | `https://api.vestradetergents.com/admin/products` | `https://admin.vestradetergents.com/products` | |
| 5 | `https://api.vestradetergents.com/admin/customers` | `https://admin.vestradetergents.com/customers` | |

## Defects

| ID | Severity | Description | Steps to Reproduce | Status |
|----|----------|-------------|--------------------|--------|
| | | | | |

## Conclusion

- [ ] Customer website regression passed.
- [ ] API regression passed.
- [ ] Admin portal regression passed.
- [ ] Legacy redirects passed.
- [ ] No critical defects outstanding.
