# View Details Implementation — Phase 13.31

## Problem

Alpine `open: @js($show)` did not stay in sync with Livewire `showDetailDrawer`, so `x-show` could keep the drawer hidden after open.

## Fix

- Bound visibility with `@entangle('showDetailDrawer')`.
- Enriched `EnquiryAdminService::getDetail()` with customer, enquiry, attachments, notes, and activity derived from real timestamps (`created_at`, `read_at`, `replied_at`, `updated_at`).
- Empty values render as **Not provided**.
- Removed Assigned Administrator / reassign UI from the drawer (assignment removed from workflow).

## Actions retained

Mark Resolved, reply draft/send, status update, internal notes, print.
