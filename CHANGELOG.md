# VESTRA Changelog

## [4.0.0] — Production Release Candidate

### Security
- Added Content-Security-Policy (CSP) headers
- Added X-Frame-Options, X-Content-Type-Options, Referrer-Policy
- Added HSTS for production
- Added API rate limiting (60/min public, 120/min auth, 5/min login)
- Added TrustProxies middleware for load balancers
- Added Permissions-Policy header
- Removed server fingerprinting headers
- Enabled session encryption and SameSite=Strict cookies

### DevOps & Deployment
- Created production Dockerfile for backend (PHP-FPM + Nginx)
- Created multi-stage Dockerfile for frontend (Next.js standalone)
- Created `docker-compose.prod.yml` with MySQL, Redis, health checks
- Created `docker-compose.dev.yml` for local development
- Added GitHub Actions CI pipeline (lint, type-check, build, Docker)
- Added GitHub Actions deploy pipeline (build, push, SSH deploy)
- Added Nginx configuration with gzip, caching, security headers
- Added PHP opcache configuration for production

### Monitoring & Observability
- Added `/api/v1/health` endpoint with DB, storage, cache checks
- Added `/api/v1/health/ready` and `/api/v1/health/live` probes
- Added frontend `/api/health` endpoint
- Added JSON log formatter for structured logging
- Added backup and restore scripts
- Added Docker health checks for all services

### SEO & Performance
- Added dynamic `sitemap.xml` (Next.js App Router)
- Added dynamic `robots.txt` with disallow rules
- Added PWA `manifest.json`
- Enabled Next.js Image Optimization (removed `unoptimized: true`)
- Added `output: 'standalone'` for efficient Docker deployment
- Added `optimizePackageImports` for `lucide-react`
- Added security headers in Next.js config

### Accessibility
- Added Skip to Main Content link
- Added VisuallyHidden component for screen readers
- Added `lang="en"` to HTML element

### Documentation
- Added `DEPLOYMENT.md` — Step-by-step deployment guide
- Added `OPERATIONS.md` — Daily operations and incident response
- Added `ENVIRONMENT.md` — Environment variables reference
- Added `ARCHITECTURE.md` — System architecture and data flow
- Added `SECURITY.md` — Security checklist and incident response
- Added `CHANGELOG.md` — Release notes

## [3.1.0] — Commerce Completion

### Payments
- Integrated Flutterwave payment gateway
- Added MTN Mobile Money, Airtel Money, Card payment support
- Added payment initiation, callback, and verification flow
- Added payment transaction tracking
- Added idempotent callback processing

### Customer Portal
- Added `/account/orders` page with order list
- Added `/account/orders/{id}` page with timeline and invoice download
- Added `/account/addresses` page with CRUD
- Added `/account/settings` page with profile and password

### Notifications
- Added email notifications: Order Confirmation, Payment Confirmation, Shipping, Delivery
- Added OrderObserver for automatic notification triggers

### PDF Invoices
- Added PDF invoice generation via dompdf
- Added invoice download from customer portal and API

### Admin Enhancements
- Added order status actions (Mark Paid, Processing, Packed, Shipped, Delivered, Cancel, Refund)
- Added fulfilment fields (courier, tracking, dispatch dates)
- Added status history tracking
- Added dashboard widgets (Revenue, Orders, Low Stock)

### Reporting
- Added dashboard summary API
- Added sales trend API
- Added best sellers API
- Added inventory value API
- Added customer growth API

## [3.0.0] — E-Commerce Foundation

### Customer Authentication
- Added customer registration and login
- Added password reset and change password
- Added Sanctum token authentication
- Added customer profile management

### Shopping Cart
- Added add/remove/update quantity
- Added guest cart (localStorage) and authenticated cart (database)
- Added cart merge on login

### Checkout
- Added multi-step checkout (shipping, payment, review)
- Added Cash on Delivery payment
- Added address selection

### Orders
- Added order creation and invoice number generation
- Added order status tracking
- Added order history in customer portal

### Admin
- Added OrderResource in Filament
- Added CustomerResource in Filament
- Added inventory management

## [2.2.0] — Frontend/Backend Integration

- Replaced all static data with live API calls
- Created centralized API client
- Added TanStack Query for server state
- Added loading, error, and empty states
- Added type-safe API services

## [2.0.0] — Frontend Development

- Next.js 15 App Router
- Tailwind CSS design system
- Responsive layouts
- Product catalog, product detail, contact, distributor pages

## [1.0.0] — Backend Development

- Laravel 11 API
- Filament 3 admin panel
- MySQL database
- Product, category, settings management
