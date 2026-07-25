# RG1 — Release Notes

## VESTRA Commerce Platform Version 1.0.0

**Release date:** TBD  
**Release gate:** RG1  
**Status:** Release Candidate / Certified

## Overview

Version 1.0.0 of the VESTRA Commerce Platform delivers a complete retail e-commerce experience with integrated Flutterwave payments, order tracking, invoicing, and a dedicated Filament administration portal.

## What's Included

### Customer Shopping Journey (Stage 18.1)

- Clear separation of retail, distributor, and bulk-order journeys.
- Redesigned navigation with Products, Distributor, Bulk Orders, and Account.
- Product pages prioritise purchasing with gallery, price, quantity selector, Add to Cart, and Buy Now.
- Distributor application moved to a separate flow with dedicated success page.

### Shopping Cart (Stage 18.2)

- Persistent guest and authenticated carts.
- Cart drawer and full `/cart` page.
- Quantity management with stock validation.
- Toast notifications and empty-cart UX.
- Buy Now flow.

### Checkout & Order Creation (Stage 18.3)

- Guest and registered checkout.
- Delivery address collection.
- Shipping method selection with cost estimation.
- Dynamic checkout summary.
- Order creation with stock validation.
- Pre-payment order confirmation.

### Flutterwave Payment Lifecycle (Stage 18.4)

- MTN Mobile Money, Airtel Money, Visa/Mastercard, Bank Transfer.
- Payment initiation, callback handling, webhook processing.
- Order and payment status lifecycle.
- Retry payments without duplicate orders.
- Customer payment history and admin payment visibility.

### Production Domain Architecture (Stages 18.4A & 18.4A.1)

- Admin portal migrated to `https://admin.vestradetergents.com`.
- API domain serves REST API only.
- Legacy `/admin/*` URLs redirect correctly without preserving `/admin`.

### Customer Orders, Tracking & Post-Purchase (Stage 18.5)

- Customer order history and order details.
- Visual order timeline.
- Order tracking by order/invoice number.
- Invoice generation and PDF download.
- Customer dashboard with quick actions.
- Admin fulfilment workflow with audit history.

## Deployment Information

| Item | Value |
|------|-------|
| Production domains | `vestradetergents.com`, `api.vestradetergents.com`, `admin.vestradetergents.com` |
| Deployment root | `/opt/vestra` |
| Docker Compose | `docker-compose.prod.yml` |
| Environment | `.env.production` |

## Known Issues

See `11-known-issues.md`.

## Upcoming Work

- Stage 18.6 — Customer Account, Profile & Self-Service Portal.

## Certification

This release has been certified for production. See `15-version-certification.md`.
