# VESTRA — Operations Runbook

Day-to-day operation, monitoring and incident response.

Throughout, `DC` abbreviates:

```bash
alias DC='docker compose -f /opt/vestra/docker-compose.prod.yml --env-file /opt/vestra/.env.production'
```

---

## 1. Services

| Service | Role | Restart is safe? |
|---|---|---|
| `nginx` | TLS termination, sole ingress | Yes — brief connection reset |
| `frontend` | Next.js SSR | Yes |
| `backend` | API + Filament admin; **owns migrations** | Yes |
| `queue` | Processes the Redis queue | Yes — in-flight job retries |
| `scheduler` | Runs the task schedule | Yes |
| `db` | MySQL 8 | **Caution** — see §6 |
| `redis` | Cache, queue, sessions | **Caution** — active sessions drop |
| `certbot` | Certificate renewal | Yes |

Restarting `redis` logs every user out and, despite AOF, may lose the tail of
the queue. Prefer restarting only the service that is actually misbehaving.

---

## 2. Health checks

| Endpoint | Meaning |
|---|---|
| `GET /api/v1/health` | Full check — DB, cache, storage. **503** when any fails |
| `GET /api/v1/health/ready` | Readiness — DB, cache, Redis. **503** when not ready |
| `GET /api/v1/health/live` | Liveness — process only, no dependencies |
| `GET /api/health` (frontend) | Next.js is serving |
| `GET /nginx-health` | nginx is up; says nothing about the app |

```bash
DC ps                                   # all should be "healthy"
curl -fsS https://api.vestra.com/api/v1/health | jq
```

`liveness` deliberately checks nothing external: a liveness probe that fails on
a transient database blip would have the orchestrator kill a healthy container,
turning a short outage into a crash loop.

---

## 3. Daily checks

```bash
DC ps                                          # all healthy
df -h /                                        # disk under 70%
free -h                                        # memory headroom
ls -la /opt/vestra/backups | tail -3           # last night's backup exists
DC logs --since 24h backend | grep -i error | head -50
DC exec redis redis-cli -a "$REDIS_PASSWORD" --no-auth-warning llen queues:default
```

A `queues:default` length that climbs and never falls means the worker is stuck
— see §5.

---

## 4. Logs

```bash
DC logs -f backend                # follow
DC logs --since 1h --tail 200 queue
DC logs backend | grep -i "error\|exception\|critical"
```

Docker rotates at 10 MB × 3 files per service (`docker-compose.prod.yml`
`x-logging`). Without that cap, `LOG_CHANNEL=stderr` fills the disk.

Application-level audit records live in the `audit_logs` table, not the
container log:

```bash
DC exec db mysql -u root -p"$MYSQL_ROOT_PASSWORD" vestra -e \
  "SELECT created_at, action, user_id, ip_address FROM audit_logs ORDER BY created_at DESC LIMIT 20;"
```

Security-relevant actions recorded there include `login.lockout`,
`authorization.denied` and `security.default_password_in_use`.

---

## 5. Queue

```bash
DC logs --tail 100 queue
DC exec redis redis-cli -a "$REDIS_PASSWORD" --no-auth-warning llen queues:default
DC exec backend php artisan queue:failed
DC exec backend php artisan queue:retry all
DC restart queue
```

The worker runs with `--max-time=3600 --max-jobs=1000`; it exits periodically by
design and `restart: unless-stopped` brings it back. That bounds memory leaks in
long-lived PHP processes. Seeing the queue container restart hourly is **normal**.

> After deploying code that changes a job class, restart the worker. Workers
> hold the old class in memory until they exit.

---

## 6. Scheduler

```bash
DC exec scheduler php artisan schedule:list
DC logs --tail 50 scheduler
```

Expected: `auth:cleanup-exchange-tokens` and `sanctum:cleanup-expired`, hourly.

If the scheduler is down, expired authentication tokens are never pruned. That
is a slow-building security issue, not merely untidy — treat a stopped
scheduler as a real incident.

---

## 7. Common incidents

### Site down — 502 from nginx

```bash
DC ps
DC logs --tail 100 backend
DC exec backend curl -fsS localhost:8080/api/v1/health
DC restart backend
```

If health reports `"database": false`, go to §8.

### Backend restart loop

```bash
DC logs --tail 200 backend | grep -i "entrypoint\|FATAL"
```

The entrypoint exits deliberately when MySQL is unreachable after 60 attempts
(~2 min) or Redis after 30. Fix the dependency, not the backend.

### Slow responses

```bash
DC stats --no-stream
DC exec db mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SHOW FULL PROCESSLIST;"
DC exec db tail -50 /var/lib/mysql/slow.log     # queries over 1s
```

### Redis out of memory

```bash
DC exec redis redis-cli -a "$REDIS_PASSWORD" --no-auth-warning info memory
```

The policy is `noeviction` **by design** — this instance backs the queue and
sessions, so evicting would silently discard jobs and log users out. When it
fills, writes fail loudly instead. Clear cache first; only then consider raising
`--maxmemory`:

```bash
DC exec backend php artisan cache:clear
```

### Certificate expiry

```bash
DC exec certbot certbot certificates
echo | openssl s_client -connect vestra.com:443 2>/dev/null | openssl x509 -noout -dates
DC exec certbot certbot renew --force-renewal
DC restart nginx     # nginx must reload to pick up a new certificate
```

Renewal is automatic twice daily, but nginx does **not** notice a new
certificate on its own. If a renewal lands without a reload, the old
certificate keeps being served until nginx restarts.

---

## 8. Database

```bash
DC exec db mysqladmin ping -u root -p"$MYSQL_ROOT_PASSWORD"
DC exec db mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SHOW STATUS LIKE 'Threads_connected';"
DC exec backend php artisan migrate:status
```

Never run `migrate:fresh`, `migrate:reset` or `db:wipe` against production —
each destroys all data. The production entrypoint never seeds for the same
reason.

---

## 9. Maintenance mode

```bash
DC exec backend php artisan down --retry=60 --secret="<random-token>"
# operators reach the site via https://api.vestra.com/<random-token>
DC exec backend php artisan up
```

---

## 10. Alerting thresholds

| Metric | Warning | Critical |
|---|---|---|
| HTTP 5xx rate | > 1% | > 5% |
| p95 response time | > 500 ms | > 2 s |
| Disk usage | > 70% | > 90% |
| Memory usage | > 70% | > 90% |
| Queue depth | > 100 | > 1000 |
| Failed jobs | > 10/h | > 50/h |
| Certificate expiry | < 21 days | < 7 days |
| Last successful backup | > 26 h | > 48 h |

---

## 11. Incident response

1. **Assess** — `DC ps`, health endpoints, scope of impact.
2. **Communicate** — notify stakeholders; enable maintenance mode if the outage
   is user-visible and expected to last.
3. **Contain** — if the incident followed a deploy, roll back first and
   diagnose afterwards: `./scripts/rollback.sh`.
4. **Resolve** — apply the fix; verify via health endpoints and a real user
   journey, not just container status.
5. **Review** — record root cause, timeline and follow-up actions; update this
   runbook if the path taken was not documented.

### Escalation

| Area | Path |
|---|---|
| Application errors | Backend logs → `audit_logs` → development team |
| Payments | Flutterwave dashboard → webhook delivery log → provider support |
| Infrastructure | VPS provider status page → support ticket |
| TLS / DNS | Registrar and Let's Encrypt status |
