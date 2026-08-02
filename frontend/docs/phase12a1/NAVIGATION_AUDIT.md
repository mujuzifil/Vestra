# Phase 12A.1 — Distributor Navigation Audit

## Scope

Audit every "Become a Distributor" CTA and every `/distributor` or `/account` route usage in the public-facing frontend to ensure no public navigation sends users into the legacy customer account dashboard.

## Audit Method

Search terms:

- `"Become a Distributor"`
- `/distributor`
- `/account`
- `router.push`
- `redirect_to`

Files reviewed manually where redirects or conditional routing were suspected.

## Public "Become a Distributor" CTAs

| File | Link Text | Href | Status |
|------|-----------|------|--------|
| `components/navigation/navbar.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `components/layout/footer.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `components/common/cta-section.tsx` | *prop-driven* | *caller's href* | ✅ Generic component, no conditional logic |
| `components/sections/hero-section.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `components/sections/contact-banner-section.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `components/forms/quote-form.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `components/forms/contact-form.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/page.tsx` | (via DistributorCtaSection) | `/distributor` | ✅ Correct |
| `app/products/page.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/products/[slug]/product-page-client.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/request-quote/_components/related-resources-section.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/where-to-buy/_components/where-to-buy-hero.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/where-to-buy/_components/become-distributor-cta-section.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/where-to-buy/_components/final-cta-section.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/contact/contact-page-client.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/blog/_components/final-cta-section.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/blog/_components/resources-section.tsx` | Become a Distributor | `/distributor` | ✅ Correct |
| `app/about/about-page-client.tsx` | Become a Distributor | `/distributor` | ✅ Correct |

## Internal / Conditional Routing

| File | Finding | Action |
|------|---------|--------|
| `app/distributor/page.tsx` — `ApplicationStatusCard` pending | Link to `/account` with text "Go to My Account" | 🛠️ Fixed → `/distributor` "Return to Application" |
| `components/layout/distributor-layout.tsx` | Authenticated non-distributors redirected to `/account` | 🛠️ Fixed → `/distributor` |
| `components/distributor/distributor-sidebar.tsx` | "Customer Portal" link to `/account` | 🛠️ Removed |
| `components/layout/customer-layout.tsx` | Link to `/distributor` labeled "Distributor Portal" | 🛠️ Relabeled to "Become a Distributor" |
| `components/navigation/navbar.tsx` | Account dropdown links to `/account` and `/account/orders` | ✅ Out of scope — legitimate account navigation |
| `app/auth/login/login-page-client.tsx` | Falls back to `/account` when `redirect_to` is empty | ✅ Out of scope — post-auth routing, account portal redesign pending |
| `app/auth/register/register-page-client.tsx` | Redirects to `/account` after registration | ✅ Out of scope — post-auth routing, account portal redesign pending |

## Legacy Commerce Routes

| Route | Current Handling | Status |
|-------|------------------|--------|
| `/cart` | Redirects to `/request-quote` in `next.config.ts` | ✅ Correct |
| `/checkout` | Redirects to `/request-quote` in `next.config.ts` | ✅ Correct |
| `/compare` | Redirects to `/products` in `next.config.ts` | ✅ Correct |
| `/bulk-orders` | Redirects to `/request-quote` in `next.config.ts` | ✅ Correct |
| `/track` | Redirects to `/account/orders` in `next.config.ts` | ✅ Accepted per Phase 1.5 |

## Summary

All public "Become a Distributor" CTAs already pointed to `/distributor`. The incorrect routing into `/account` was caused by two internal redirects and one public CTA inside the distributor application status card. All three have been corrected.
