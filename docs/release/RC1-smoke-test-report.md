# RC1 — Smoke Test Report

## Objective

Execute a complete end-to-end customer journey to confirm that browsing, cart, checkout, payment, order tracking, invoice download, and admin fulfilment work together.

## Test Scenario

A customer browses products, adds one to the cart, checks out, pays via Flutterwave, receives an order confirmation, tracks the order, downloads an invoice, and the admin updates the order status which is reflected in the customer timeline.

## Step-by-Step Results

| Step | Action | Expected Result | Actual Result | Status |
|------|--------|-----------------|---------------|--------|
| 1 | Visit `https://vestradetergents.com/` | Homepage loads | | |
| 2 | Navigate to Products | Product grid visible | | |
| 3 | Select a product | Product detail page loads with Add to Cart | | |
| 4 | Set quantity and click Add to Cart | Cart badge updates, toast shown | | |
| 5 | Open cart drawer / go to `/cart` | Cart item listed | | |
| 6 | Click Proceed to Checkout | Checkout form loads | | |
| 7 | Fill customer and address information | Validation passes | | |
| 8 | Select shipping method | Shipping cost updates | | |
| 9 | Review order summary | Totals correct | | |
| 10 | Click Place Order | Order created, redirect to confirmation | | |
| 11 | Click Proceed to Payment | Flutterwave checkout opens | | |
| 12 | Complete payment | Payment success, callback handled | | |
| 13 | View order confirmation | Payment successful, transaction ref shown | | |
| 14 | Go to `/account/orders` | New order visible | | |
| 15 | Open order detail | Items, totals, timeline shown | | |
| 16 | Download invoice | PDF generated and downloadable | | |
| 17 | Track order | Current status and timeline visible | | |
| 18 | Admin logs in at `https://admin.vestradetergents.com` | Dashboard loads | | |
| 19 | Admin opens the order | Order details correct | | |
| 20 | Admin updates status (e.g. Preparing → Packed → Dispatched) | Status saved | | |
| 21 | Customer refreshes order detail | Timeline shows new status | | |

## Payment Test Details

| Property | Value |
|----------|-------|
| Order number | |
| Amount | |
| Payment method | |
| Transaction reference | |
| Payment status | |
| Order status | |

## Issues

| Step | Issue | Severity | Resolution |
|------|-------|----------|------------|
| | | | |

## Conclusion

- [ ] End-to-end customer journey completed.
- [ ] Payment processed successfully.
- [ ] Order tracking updated correctly.
- [ ] Invoice generated.
- [ ] Admin fulfilment workflow functional.
