# Approval Workflow Fix

## Problem

Selecting **Approve** could return a **500 SERVER ERROR** in real environments even though feature tests passed.

## Root Causes Addressed

1. **Synchronous notification/mail inside the DB transaction** — `DistributorApprovedNotification` ran during the transaction. Mail/driver failures rolled back the whole approval and surfaced as 500.
2. **Unique `distributors.user_id` collisions** — approving an applicant whose email already mapped to an existing distributor user violated the unique constraint.
3. **Idempotency gaps** — re-approving a request that already had a distributor record could attempt a second create.
4. **Unsafe field coercion** — `years_in_business` (tinyint) and array-shaped product interests could violate column constraints.

## Fix (`DistributorOnboardingService`)

Approve now:

- Runs inside `DB::transaction`
- Validates application state (rejects cannot be approved; duplicate user distributor fails with `ValidationException`)
- Idempotently returns an existing distributor for the same request
- Creates distributor + default branch/contact + credit account + distributor role
- Writes **Audit Log** (`distributor_approved`) inside the transaction
- Dispatches notification + `DistributorApplicationApproved` via `DB::afterCommit`
- Makes `DistributorApprovedNotification` implement `ShouldQueue`

Reject now:

- Runs inside a transaction
- Blocks rejecting already-approved applications
- Writes **Audit Log** (`distributor_rejected`)
- Dispatches `DistributorApplicationRejected` after commit

## UI

`ApplicationsPage` approve/reject still authorize via policy, close the detail drawer when relevant, and emit Filament success notifications. Livewire re-renders KPIs and the table from live queries.
