# Phase 12A.1 — Routing Changes

## Changed Files

### 1. `frontend/app/distributor/page.tsx`

**What changed:**

In the `ApplicationStatusCard` component, the pending-application state previously showed a link to `/account`:

```tsx
<Link href="/account" ...>
  Go to My Account
</Link>
```

This has been replaced with:

```tsx
<Link href="/distributor" ...>
  Return to Application
</Link>
```

**Why:** Authenticated users with a pending distributor application were being sent to the legacy customer account dashboard from a public distributor page. They should remain in the distributor workflow.

### 2. `frontend/components/layout/distributor-layout.tsx`

**What changed:**

The guard for authenticated users who do **not** have the `distributor` role previously redirected to `/account`:

```tsx
if (!isLoading && isAuthenticated && user && !user.roles?.includes("distributor")) {
  router.push("/account");
}
```

Now redirects to the public distributor application page:

```tsx
if (!isLoading && isAuthenticated && user && !user.roles?.includes("distributor")) {
  router.push("/distributor");
}
```

The unauthenticated guard still redirects to `/auth/login` because distributor portal pages require authentication.

**Why:** Non-distributor users who land on a distributor portal route should be encouraged to apply, not dropped into the customer account dashboard.

### 3. `frontend/components/distributor/distributor-sidebar.tsx`

**What changed:**

Removed the "Customer Portal" link that pointed to `/account` from the bottom of the distributor sidebar.

**Why:** A user inside the distributor portal does not need a primary-navigation link back to the legacy customer dashboard. This also eliminates a second route into `/account` from the distributor workflow.

### 4. `frontend/components/layout/customer-layout.tsx`

**What changed:**

Relabeled the sidebar link to `/distributor` from "Distributor Portal" to "Become a Distributor".

**Why:** Since `/distributor` is now the public application page, the old label incorrectly suggested it was the authenticated distributor portal.

## Unchanged Files

- `components/navigation/navbar.tsx` — account dropdown correctly links to account pages for signed-in users.
- `app/auth/login/login-page-client.tsx` — post-auth fallback to `/account` left unchanged; addressed in later account-portal redesign.
- `app/auth/register/register-page-client.tsx` — post-auth redirect to `/account` left unchanged; addressed in later account-portal redesign.
- `next.config.ts` legacy redirects left unchanged.
