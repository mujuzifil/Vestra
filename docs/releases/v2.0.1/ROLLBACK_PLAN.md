# VESTRA v2.0.1 — Rollback Plan

## Rollback Trigger

Use this plan only if a critical, unresolvable issue is discovered after deploying v2.0.1.

## Pre-Rollback

1. Announce maintenance window.
2. Stop any active marketing or traffic-driving activities.
3. Verify the previous image is available on the server:
   ```bash
   docker images vestra/vestra-backend vestra/vestra-frontend
   ```

## Rollback Steps

1. SSH into the production server:
   ```bash
   ssh -i ~/.ssh/id_ed25519 deploy@187.77.84.119
   ```

2. Navigate to the project directory:
   ```bash
   cd /opt/vestra
   ```

3. Set the previous image tag:
   ```bash
   export IMAGE_TAG=local-20260801232957
   sed -i "s/^IMAGE_TAG=.*/IMAGE_TAG=${IMAGE_TAG}/" .env.production
   ```

4. Recreate containers without rebuilding:
   ```bash
   docker compose -f docker-compose.prod.yml --env-file .env.production up -d --no-build
   ```

5. Verify container health:
   ```bash
   docker compose -f docker-compose.prod.yml --env-file .env.production ps
   ```

6. Verify API health:
   ```bash
   curl https://api.vestradetergents.com/api/v1/health
   ```

7. Verify public website:
   ```bash
   curl -I https://vestradetergents.com/
   ```

## Database Considerations

- All v2.0.x migrations are additive.
- A code-only rollback does not require database changes.
- If a rollback of database state is required, restore from the pre-deployment backup:
  ```bash
  /opt/vestra/backups/20260801_232104
  ```

## Post-Rollback

1. Monitor error logs for 15 minutes.
2. Confirm public pages respond with 200.
3. Confirm API health returns 200.
4. Update `PREVIOUS_TAG` in `.env.production` if necessary.

## Contact

If rollback does not resolve the issue, investigate infrastructure (Docker, Nginx, MySQL, Redis) before re-attempting deployment.
