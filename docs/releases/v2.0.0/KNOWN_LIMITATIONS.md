# VESTRA v2.0.0 — Known Limitations

## Accepted at Launch

1. **Legacy commerce subsystem remains**
   - Cart, checkout, wishlist, saved-for-later, and payment API endpoints are still registered.
   - `carts` and `cart_items` tables remain in the database.
   - They are not reachable from the public B2B website.
   - Removal is scheduled for a future backend cleanup phase.

2. **Blog content is CMS-ready but empty**
   - The Knowledge Centre UI and backend CMS exist.
   - Live articles must be authored and published through Filament before the blog is populated.

3. **Distributor directory is architecture-ready**
   - The "Where to Buy" page presents the network concept.
   - Live distributor records must be added and approved before the directory is fully populated.

4. **SMS notifications use the log provider**
   - The platform is event-ready for Twilio/AWS SNS but currently logs SMS instead of sending them.

5. **Pint code-style backlog**
   - A large inherited style backlog exists. Style checks run in advisory mode in CI.

6. **Browser QA and Lighthouse**
   - Full cross-browser/device QA and Lighthouse scoring must be performed from a real browser environment.
   - This release relies on automated CI build/typecheck and local static review.

## Follow-Up Work

- Complete backend commerce cleanup.
- Populate blog articles and SEO metadata.
- Add approved distributor records.
- Integrate production SMS provider.
- Address Pint style backlog.
- Run Lighthouse CI and accessibility scans.
