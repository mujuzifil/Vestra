# VESTRA v2.0.0 — Launch Checklist

## Pre-Launch

- [x] `develop` merged into `master`
- [x] Tag `v2.0.0` created and pushed
- [x] GitHub Actions deploy workflow triggered
- [x] Pre-deploy backup taken automatically by `deploy.yml`

## Post-Launch Verification

- [ ] All services healthy (`docker compose ps`)
- [ ] Backend health endpoint returns 200
- [ ] Frontend health endpoint returns 200
- [ ] HTTPS redirect and HSTS present
- [ ] SSL certificate valid
- [ ] Homepage loads
- [ ] Products page loads with images
- [ ] Quote form submits and emails send
- [ ] Distributor form submits and emails send
- [ ] Contact form submits and emails send
- [ ] Customer login/register works
- [ ] Admin login works
- [ ] Filament resources accessible
- [ ] Queue drains
- [ ] Scheduler commands listed
- [ ] No pending migrations
- [ ] Backup from last 26 hours exists

## Sign-Off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Engineering | | | |
| Operations | | | |
| Business Owner | | | |

## Abort Criteria

Roll back immediately if:

- Payments fail or double-charge
- Authentication broken
- 5xx rate > 5%
- Data loss suspected
- Security control not enforced

Rollback command:

```bash
cd /opt/vestra
./scripts/rollback.sh
```
