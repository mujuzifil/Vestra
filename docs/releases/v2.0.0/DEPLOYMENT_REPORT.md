# VESTRA v2.0.0 — Deployment Report

## Release Metadata

| Field | Value |
|-------|-------|
| Version | v2.0.0 |
| Release date | 2026-08-01 |
| Merge commit | `d426966` |
| Previous release | v1.0.0 (see `docs/releases/v1.0.0/`) |
| Deployed by | GitHub Actions `deploy.yml` |
| Workflow run | https://github.com/mujuzifil/Vestra/actions/runs/30716076211 |
| Tag | https://github.com/mujuzifil/Vestra/releases/tag/v2.0.0 |

## Pre-Flight

- [x] Working tree cleaned of temporary/debug files.
- [x] Unrelated local changes to `frontend/Dockerfile` and `frontend/package-lock.json` were reset and excluded.
- [x] `develop` merged into `master` with `--no-ff`.
- [x] Release tag `v2.0.0` created and pushed.
- [x] `docs/release/KNOWN_ISSUES.md` reviewed.

## Deployment Outcome

| Field | Value |
|-------|-------|
| Workflow status | **Failed** |
| Failed step | `Log in to registry` |
| Failed job | `Build & Push Images` |
| Production deployed | **No** |

The deploy workflow started automatically on the `master` push but failed before any image was built. The failure occurred at the Docker registry login step, which indicates that the repository secrets `DOCKER_USERNAME` and/or `DOCKER_PASSWORD` are missing or incorrect.

Because the login step failed, no images were pushed and the VPS was not contacted. The previous production release remains running.

## Deployment Method (Planned)

The existing GitHub Actions `deploy.yml` workflow is intended to:

1. Build backend and frontend production images.
2. Push images to the Docker registry and tag them with the short SHA plus `latest`.
3. SSH into the production VPS (`/opt/vestra`), record `PREVIOUS_TAG`, update `IMAGE_TAG`, pull images, run `php artisan migrate --force`, and recreate containers.
4. Poll backend `/api/v1/health` for up to 5 minutes.

## Migration Summary

Once the deployment succeeds, the following migrations from Phases 1.5–11 will be applied:

- `2026_08_01_000001_create_quote_requests_table`
- `2026_08_01_000002_create_quote_request_items_table`
- `2026_08_01_090000_add_requirements_to_quote_requests_table`
- `2026_08_01_100000_add_crm_and_attachments_to_quote_requests_table`
- Blog CMS tables (`blog_authors`, `blog_categories`, `blog_tags`, `blog_posts`, etc.)
- `enhance_contact_messages_table`

All migrations are additive; rollback is safe for code-only rollbacks.

## Planned Services

| Service | Image | Notes |
|---------|-------|-------|
| nginx | `nginx:1.27-alpine` | Reverse proxy and TLS termination |
| frontend | `vestra-frontend:v2.0.0` | Next.js standalone |
| backend | `vestra-backend:v2.0.0` | PHP-FPM + Nginx |
| queue | `vestra-backend:v2.0.0` | Redis queue worker |
| scheduler | `vestra-backend:v2.0.0` | Laravel scheduler |
| db | `mysql:8.0` | Database |
| redis | `redis:7-alpine` | Cache / sessions / queue |
| certbot | `certbot/certbot:latest` | Let's Encrypt renewal |

## Recovery Steps

1. **Fix registry secrets**
   - Go to `Settings > Secrets and variables > Actions` in the GitHub repository.
   - Verify or set `DOCKER_USERNAME` and `DOCKER_PASSWORD`.
   - Re-run the failed workflow, **or** push a small commit to `master`.

2. **Alternative: manual VPS deploy**
   - SSH into the VPS as the deploy user.
   - Run:
     ```bash
     cd /opt/vestra
     git fetch origin
     git checkout v2.0.0
     ./scripts/deploy.sh --build
     ```

## Operator Notes

- Post-deploy verification must be completed per `PRODUCTION_VALIDATION.md` once the deployment succeeds.
- Rollback instructions are in `ROLLBACK_PLAN.md`.
