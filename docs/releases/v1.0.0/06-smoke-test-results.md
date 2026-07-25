# RG1 — Smoke Test Results

## Objective

Validate the complete end-to-end customer purchase journey and admin fulfilment workflow.

## Test Scenario

A registered customer browses products, adds one to the cart, completes checkout, pays via Flutterwave, downloads an invoice, tracks the order, and an administrator updates the order status which is reflected in the customer timeline.

## Step-by-Step Results

| Step | Action | Expected Result | Actual Result | Status | Notes |
|------|--------|-----------------|---------------|--------|-------|
| 1 | Visit homepage | Homepage loads | | | |
| 2 | Navigate to Products | Product grid visible | | | |
| 3 | Select a product | Product detail page loads | | | |
| 4 | Set quantity and Add to Cart | Cart badge updates | | | |
| 5 | Open cart / go to `/cart` | Item listed with totals | | | |
| 6 | Proceed to checkout | Checkout form loads | | | |
| 7 | Enter customer information | Validation passes | | | |
| 8 | Enter delivery address | Validation passes | | | |
| 9 | Select shipping method | Cost updates in summary | | | |
| 10 | Review order summary | Totals correct | | | |
| 11 | Place order | Order created, redirect to confirmation | | | |
| 12 | Proceed to payment | Flutterwave checkout opens | | | |
| 13 | Complete payment | Payment successful | | | |
| 14 | View order confirmation | Transaction reference shown | | | |
| 15 | Go to customer orders | New order visible | | | |
| 16 | Open order detail | Items, totals, timeline shown | | | |
| 17 | Download invoice | PDF generated | | | |
| 18 | Track order | Status and timeline visible | | | |
| 19 | Admin logs in | Dashboard loads | | | |
| 20 | Admin opens order | Order details correct | | | |
| 21 | Admin updates status to Preparing | Status saved | | | |
| 22 | Admin updates status to Packed | Status saved | | | |
| 23 | Admin updates status to Dispatched | Status saved | | | |
| 24 | Customer refreshes order detail | Timeline reflects updates | | | |

## Payment Details

| Property | Value |
|----------|-------|
| Order number | |
| Order ID | |
| Amount | |
| Currency | |
| Payment method | |
| Flutterwave transaction reference | |
| Payment status | |
| Order status after payment | |

## Defects

| ID | Severity | Description | Status |
|----|----------|-------------|--------|
| | | | |

## Conclusion

- [ ] End-to-end customer journey completed.
- [ ] Payment processed successfully.
- [ ] Invoice generated.
- [ ] Order tracking updated.
- [ ] Admin fulfilment workflow functional.
- [ ] Customer timeline reflects admin updates.
