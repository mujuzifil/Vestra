# VESTRA v2.0.0 — Deployment Report

> **Update:** The v2.0.0 GitHub Actions deployment failed due to missing Docker registry credentials. The release was subsequently deployed manually on the production VPS as **v2.0.1**, which includes the v2.0.0 feature set plus deployment-time hotfixes. See `docs/releases/v2.0.1/` for the actual production deployment report.

## Release Metadata

| Field | Value |
|-------|-------|
| Version | v2.0.0 |
| Release date | 2026-08-01 |
| Merge commit | `d426966` |
| Previous release | v1.0.0 (see `docs/releases/v1.0.0/`) |
| Tag | https://github.com/mujuzifil/Vestra/releases/tag/v2.0.0 |

## Original Deployment Attempt

The v2.0.0 release was tagged and pushed to `master`, triggering the GitHub Actions `deploy.yml` workflow. The workflow failed at the Docker registry login step because `DOCKER_USERNAME` and `DOCKER_PASSWORD` repository secrets were missing or incorrect. No images were built and the VPS was not contacted.

## Manual Deployment (v2.0.1)

Following the failed automated deployment, the release was deployed manually on the VPS. During this process the following hotfixes were applied:

1. Nginx duplicate `default_server` conflict resolved.
2. Frontend runtime image updated to include `curl` for health probes.
3. PHP syntax error in `app/Filament/Resources/BlogPostResource.php` fixed.

See `docs/releases/v2.0.1/DEPLOYMENT_REPORT.md` for full details.
