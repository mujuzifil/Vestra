# Stage 24.9 Frontend Sync Notes

## Public distributor bridges

- `GET /api/v1/public/distributors` — active partners only; district/region filters are grouped so inactive partners cannot leak in
- `GET /api/v1/public/distributors/coverage` — coverage for active partners
- `GET /api/v1/public/distributors/{id}` — public detail
- `GET /api/v1/distributor/application-status` — includes rejection / information-request notes and linked distributor id

## Where to buy

- Directory list formats operating hours without raw JSON dumps
- Revalidation tag/path: `distributors`, `where-to-buy`, `/where-to-buy`

## Acceptance

Approve application in admin → partner appears in Active Partners and public directory.
Suspend partner → disappears from where-to-buy while historical records remain.
