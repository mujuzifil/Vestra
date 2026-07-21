# VESTRA System Architecture

## Overview

VESTRA is a full-stack e-commerce platform built with:
- **Frontend**: Next.js 15 (App Router), React 19, TypeScript, Tailwind CSS
- **Backend**: Laravel 11, PHP 8.4, MySQL 8.0, Redis 7
- **Admin**: Filament 3
- **Payments**: Flutterwave (MTN MoMo, Airtel Money, Cards)
- **Containerization**: Docker, Docker Compose

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                         Client Browser                        │
└─────────────────────────────┬───────────────────────────────┘
                              │
                    ┌─────────▼──────────┐
                    │   Nginx / CDN      │
                    │  (SSL, Gzip, Cache)│
                    └─────────┬──────────┘
                              │
              ┌───────────────┼───────────────┐
              │               │               │
     ┌────────▼────┐  ┌──────▼──────┐  ┌─────▼─────┐
     │  Next.js    │  │  Laravel    │  │  Filament │
     │  Frontend   │  │  API        │  │  Admin    │
     │  :3000      │  │  :8000      │  │  /admin   │
     └──────┬──────┘  └──────┬──────┘  └───────────┘
            │                │
            │       ┌──────▼──────┐
            │       │   MySQL     │
            │       │   :3306     │
            │       └─────────────┘
            │       ┌──────┬──────┐
            │       │      │      │
            │  ┌────▼──┐ ┌─▼────┐ ┌▼─────┐
            │  │Redis  │ │Queue │ │Cache │
            │  │:6379  │ │      │ │      │
            │  └───────┘ └──────┘ └──────┘
            │
     ┌──────▼──────┐
     │  Storage    │
     │  (S3/Local) │
     └─────────────┘
```

## Data Flow

### Customer Purchase Flow
1. Customer browses products (Next.js → Laravel API)
2. Adds items to cart (localStorage + API for authenticated)
3. Proceeds to checkout (shipping address selection)
4. Places order (COD or digital payment)
5. Digital payment → Flutterwave redirect → callback verification
6. Order status updates trigger email notifications
7. Admin manages order fulfilment via Filament

### API Communication
- Frontend uses centralized API services in `lib/api/`
- TanStack Query for server state (caching, refetching)
- Bearer token authentication via Sanctum
- All API responses follow `{ success, data, message }` format

## Database Schema

### Core Tables
- `users` — Customers and admins
- `products` — Product catalog
- `categories` — Product categories
- `orders` — Customer orders
- `order_items` — Order line items
- `carts` / `cart_items` — Shopping cart
- `customer_addresses` — Saved addresses
- `payment_transactions` — Payment records
- `order_status_history` — Order audit trail
- `settings` — CMS configuration
- `contact_messages` — Contact form submissions
- `distributor_requests` — Distributor applications

## Deployment Architecture

### Production (Docker Compose)
- `frontend` — Next.js standalone server
- `backend` — PHP-FPM + Nginx in one container
- `db` — MySQL 8.0
- `redis` — Cache, sessions, queues
- Optional: `nginx` — Reverse proxy with SSL

### Scaling Strategy
1. **Vertical**: Increase container resources
2. **Horizontal**: Multiple backend containers behind load balancer
3. **Database**: Read replicas for reporting queries
4. **CDN**: Cloudflare for static assets and DDoS protection

## Security Layers

1. **Network**: Firewall, DDoS protection, WAF
2. **Transport**: TLS 1.3, HSTS
3. **Application**: CSP, CORS, rate limiting, input validation
4. **Authentication**: Sanctum tokens, session management
5. **Authorization**: Policies, ownership checks
6. **Data**: Encryption at rest, parameterized queries
