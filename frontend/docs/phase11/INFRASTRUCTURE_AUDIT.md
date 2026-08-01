# Phase 11 — Infrastructure Audit (Frontend)

## Docker

- `frontend/Dockerfile` targets a multi-stage Node 22 build with standalone Next.js output.
- A curl installation for the health probe was added locally but is **not committed** in this phase to avoid unrelated changes.

## Nginx

- Static assets are served from the standalone output.
- Livewire dynamic routes are handled by the backend Nginx config (existing fix).

## Build Output

- Standalone build is produced in `.next/standalone`.
- Public files copied to `public/`.

## Recommendations

- Confirm health-check endpoint returns 200 in the production container.
- Review log rotation for the Next.js container.
- Keep `frontend/Dockerfile` changes separate until infrastructure hardening is approved.
