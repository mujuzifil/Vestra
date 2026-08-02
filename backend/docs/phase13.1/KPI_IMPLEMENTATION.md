# KPI Implementation

All KPIs use live backend data with a 5-minute cache and honest empty states.

## Open Quotes

- **Model:** `QuoteRequest`
- **Definition:** Count of quotes in `pending`, `contacted`, or `quoted` status.
- **Trend:** Compared to count of quotes created more than 7 days ago.

## Pending Distributor Applications

- **Model:** `DistributorRequest`
- **Definition:** Count of applications in `pending`, `under_review`, or `information_requested` status (existing `awaitingReview` scope).
- **Trend:** Compared to count awaiting review created more than 7 days ago.

## Open Support Tickets

- **Model:** `SupportTicket`
- **Definition:** Count of tickets where status is not `resolved` or `closed`.
- **Trend:** Compared to count created more than 7 days ago.

## Revenue (MTD)

- **Model:** `Order`
- **Definition:** Paid revenue from the first day of the current month through today using `Order::paidRevenueBetween()`.
- **Trend:** Compared to total paid revenue of the previous month.
- **Note:** If no paid orders exist, the value is `UGX 0`.

## Products

- **Model:** `Product`
- **Definition:** Count of products with a status other than `inactive`.
- **Trend:** Compared to count created more than 30 days ago.

## Caching

Each KPI is cached independently with a 300-second TTL. Cache keys use the prefix `admin.kpi.*`.
