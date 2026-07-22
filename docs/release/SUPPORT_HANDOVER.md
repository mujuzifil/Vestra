# VESTRA — Support Handover

Everything an operator taking over VESTRA needs on day one.

---

## What VESTRA is

An e-commerce platform for a fabric-care brand: public storefront, product
catalogue, cart and checkout, Flutterwave payments, customer accounts,
distributor enquiries, and a Filament administration panel.

| Layer | Technology |
|---|---|
| Frontend | Next.js 15 (App Router, standalone), React 19, Tailwind 4 |
| Backend | Laravel 12, PHP 8.4 |
| Admin | Filament 3 |
| Database | MySQL 8 |
| Cache / queue / sessions | Redis 7 |
| Payments | Flutterwave |
| Delivery | Docker Compose behind nginx, TLS via Let's Encrypt |

---

## Access required

Confirm each is held before accepting handover.

| System | Purpose | Holder |
|---|---|---|
| VPS SSH | Deployment, operations | |
| Git repository | Source, CI/CD | |
| Container registry | Image push/pull | |
| DNS registrar | Domain records | |
| Flutterwave dashboard | Payments, webhooks | |
| SMTP provider | Transactional mail | |
| VPS provider console | Reboot, console access | |
| Off-site backup storage | Disaster recovery | |

---

## Where things live

```
/opt/vestra/
├── .env.production          # ALL secrets. mode 600. Not in git.
├── docker-compose.prod.yml
├── nginx/                   # reverse proxy config
├── certbot/conf/live/       # TLS certificates
├── backups/                 # nightly backups
└── scripts/
    ├── deploy.sh
    ├── rollback.sh
    ├── backup.sh
    └── restore.sh
```

---

## Documentation map

| Task | Document |
|---|---|
| Deploy from scratch | [Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md) |
| Day-to-day operations, incidents | [Operations Runbook](OPERATIONS_RUNBOOK.md) |
| What every setting does | [Environment Configuration Guide](ENVIRONMENT_CONFIGURATION_GUIDE.md) |
| Backup, restore, disaster recovery | [Backup & Restore Guide](BACKUP_AND_RESTORE_GUIDE.md) |
| Verify a deployment | [Deployment Verification Checklist](DEPLOYMENT_VERIFICATION_CHECKLIST.md) |
| Roll back a bad release | [Rollback Checklist](ROLLBACK_CHECKLIST.md) |
| Launch readiness | [Go-Live Checklist](GO_LIVE_CHECKLIST.md) |
| Open risks | [Known Issues](KNOWN_ISSUES.md) |
| Credential rotation | [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md) |
| What changed | [Release Notes](RELEASE_NOTES.md) |

---

## The five things most likely to catch you out

**1. `NEXT_PUBLIC_*` are baked in at build time.**
Changing the API domain means rebuilding the frontend image. Restarting the
container achieves nothing. Symptom: browser calls `localhost:8000`.

**2. Never call `env()` outside `config/*.php`.**
`config:cache` runs on every production boot and makes `env()` return null.
Code that does this works in dev and silently uses defaults in production.
`ProductionConfigIntegrityTest` enforces this — do not weaken it.

**3. `APP_KEY` is permanent.**
Encrypted settings cannot be decrypted without the original. Never rotate it
after go-live. It is captured in every backup for this reason.

**4. Rollback does not revert the database.**
Additive migrations are safe to roll back past. Destructive ones are not — use
a restore instead. See the Rollback Checklist.

**5. Redis is `noeviction` on purpose.**
It backs the queue and sessions, not just cache. If it fills, writes fail
loudly rather than silently dropping orders. Clear the cache; do not switch to
an LRU policy.

---

## Routine tasks

| Frequency | Task |
|---|---|
| Daily | Check `$DC ps`, disk, error log, backup ran |
| Weekly | Review failed jobs, audit log, certificate expiry |
| Monthly | Apply dependency security updates; verify a restore |
| Quarterly | Full restore drill on a non-production host; review access list |

---

## Escalation

| Severity | Definition | Response |
|---|---|---|
| P1 | Site down, payments broken, data loss | Immediate |
| P2 | Major feature broken, degraded performance | Same day |
| P3 | Minor bug, cosmetic | Next release |

| Contact | Name | Method |
|---|---|---|
| Primary on-call | | |
| Engineering escalation | | |
| Business owner | | |
| VPS provider support | | |
| Flutterwave support | | |

---

## Outstanding at handover

- [ ] **Credential rotation is incomplete** — see
      [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md). Credentials
      were committed to git history and must be treated as compromised. This is
      the single most important open item.
- [ ] Git history purge not performed
- [ ] Restore drill not yet run on production-equivalent hardware
- [ ] Monitoring and alerting endpoints to be wired to your chosen provider

## Sign-off

| | Name | Date |
|---|---|---|
| Handed over by | | |
| Received by | | |
