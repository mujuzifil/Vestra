# Stage 16.0 — Production Deployment Preparation

**Status:** 🟡 READY WITH MINOR PREPARATION
**Date:** 2026-07-22
**Scope:** Pre-purchase readiness audit. No application code changes.
**Purpose:** Ensure that when the production VPS is purchased, deployment proceeds
without delays or missing dependencies.

**Related:** [Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md) ·
[Environment Configuration Guide](ENVIRONMENT_CONFIGURATION_GUIDE.md) ·
[Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md) ·
[Go-Live Checklist](GO_LIVE_CHECKLIST.md) ·
[Rollback Checklist](ROLLBACK_CHECKLIST.md) ·
[Phase 15 Certification](PHASE_15_PRODUCTION_READINESS_CERTIFICATION.md)

---

## 1. Executive Summary

VESTRA has completed Phase 9 (Security Remediation), Phase 10 (Commerce
Integrity) and Phase 15 (Production Readiness & Launch Certification). The
application stack, CI/CD pipeline, deployment scripts and operational runbooks
are all in place and verified in the repository.

This stage audited everything **outside** the codebase that must exist before
and during VPS provisioning. Findings:

- **In place (verified in repo):** deployment runbooks, Docker production stack,
  CI/CD workflow, secret generation/rotation procedures, rollback and backup
  tooling, go-live and verification checklists.
- **Not verifiable from the repo (owner action required):** production domain
  ownership and registrar/DNS access, Flutterwave live account, SMTP account,
  rotation of credentials exposed in git history, GitHub Actions secrets
  (several can only be created once the VPS exists).

Nothing technical blocks purchasing the VPS — the server IP is only needed
*after* purchase (for DNS records and GitHub secrets). Go-live, however,
remains gated on the Critical items in §9 and §10.

**Coordinator verdict: 🟡 READY WITH MINOR PREPARATION** — see §11.

---

## 2. Domain Readiness (Worker A)

The documented production hostnames throughout the codebase and runbooks are
`vestra.com`, `www.vestra.com` and `api.vestra.com`. **This domain is assumed —
ownership has not been confirmed from the repository.** If the real production
domain differs, every hostname reference in `.env.production`, nginx templates,
certbot commands and GitHub secrets must use the real one.

### Ownership checklist — must be confirmed by the owner before go-live

| Item | Status | Notes |
|---|---|---|
| Production domain registered | ☐ Unverified | `vestra.com` assumed; confirm or substitute the real domain |
| Registrar account access | ☐ Unverified | Credentials in the team password manager, 2FA enabled |
| DNS management access | ☐ Unverified | Registrar DNS or a provider (e.g. Cloudflare); confirm who can edit records |
| Root domain (`@`) | ☐ Pending VPS IP | A record → VPS |
| `www` subdomain | ☐ Pending VPS IP | A record → VPS |
| `api` subdomain | ☐ Pending VPS IP | A record → VPS |
| Domain email decision | ☐ Open | Whether email is hosted on this domain determines MX/TXT records (§3) |

**Dependency:** DNS must resolve to the VPS **before** TLS issuance — certbot's
HTTP-01 challenge validates domain control against live DNS.

---

## 3. DNS Plan (Worker A)

### Required records

| Hostname | Type | Value | TTL (pre-cutover) | TTL (steady state) |
|---|---|---|---|---|
| `vestra.com` | A | `<VPS_IP>` | 300 | 3600 |
| `www.vestra.com` | A | `<VPS_IP>` | 300 | 3600 |
| `api.vestra.com` | A | `<VPS_IP>` | 300 | 3600 |

Use low TTL (300s) until the deployment is verified stable, then raise to 3600s.
Do **not** place the API behind an orange-cloud/reverse-proxy DNS mode (e.g.
Cloudflare proxying) without reviewing `TRUSTED_PROXIES` — Laravel must see real
client IPs for rate limiting and audit logs.

### Email records (only if email is hosted on this domain)

| Type | Hostname | Value | Notes |
|---|---|---|---|
| MX | `@` | Provider's MX records | From the chosen email host |
| TXT | `@` | `v=spf1 include:<provider> ~all` | Required for deliverability of `MAIL_FROM_ADDRESS` |
| TXT | `selector._domainkey` | Provider DKIM key | From the email host |
| TXT | `_dmarc` | `v=DMARC1; p=quarantine; rua=mailto:ops@vestra.com` | Start at `p=none` if unsure, tighten later |

If transactional mail uses a third-party sender (e.g. SendGrid, Mailgun, SES)
on a subdomain, its SPF/DKIM records replace the above.

### Propagation expectations

- New records typically resolve in minutes; allow up to 24–48h for full
  resolver convergence.
- Verify from the VPS before requesting certificates:

```bash
dig +short vestra.com        # expect the VPS IP
dig +short www.vestra.com
dig +short api.vestra.com
```

---

## 4. Repository Readiness (Worker B)

| Item | Status | Value |
|---|---|---|
| Repository | ✅ Verified | `https://github.com/mujuzifil/Vestra.git` |
| Default branch | ✅ Verified | `master` |
| Production deployment branch | ✅ Verified | `master` (`.github/workflows/deploy.yml` triggers on `main`/`master`) |
| CI workflow | ✅ Verified | Build & push commit-tagged images → SSH deploy → health gate |
| GitHub permissions | ☐ Owner-confirm | Admin access needed to set Actions secrets and branch protection |

### Clone procedure (on the VPS)

```bash
sudo mkdir -p /opt/vestra && sudo chown "$USER":"$USER" /opt/vestra
git clone https://github.com/mujuzifil/Vestra.git /opt/vestra   # public repo
# or, if private:
git clone git@github.com:mujuzifil/Vestra.git /opt/vestra       # SSH deploy key
```

**SSH authentication (private repo):** generate a dedicated key on the VPS
(`ssh-keygen -t ed25519 -f ~/.ssh/vestra_deploy -N ""`), add the **public** key
as a read-only *Deploy Key* in the repository settings. No passphrase — the CI
deploy step and operators both use it non-interactively.

**PAT alternative:** a fine-grained PAT with read-only `contents` scope on this
repository only. Acceptable, but a deploy key is preferred — it cannot be used
against the API and is scoped to one repo.

### GitHub Actions secrets required

From `deploy.yml` and the Production Deployment Guide §9:

| Secret | Purpose | Available when |
|---|---|---|
| `DOCKER_USERNAME` | Registry login (also the image namespace) | Now |
| `DOCKER_PASSWORD` | Registry token (use an access token, not the account password) | Now |
| `SSH_HOST` | VPS IP or hostname | After VPS purchase |
| `SSH_USER` | Deployment user (`deploy`, §5) | After provisioning |
| `SSH_KEY` | Private key of the deployment key pair | After provisioning |
| `NEXT_PUBLIC_API_URL` | Frontend build arg — `https://api.vestra.com/api/v1` | Now |
| `NEXT_PUBLIC_SITE_URL` | Frontend build arg — `https://vestra.com` | Now |
| `NEXT_PUBLIC_BACKEND_URL` | Frontend build arg — `https://api.vestra.com` | Now |

The three `NEXT_PUBLIC_*` secrets are **compiled into the client bundle at build
time**. Changing them later requires a rebuild and redeploy — restarting the
container does nothing.

### Branch protection recommendations (`master`)

- Require a pull request with at least one approving review.
- Require the `ci.yml` status checks to pass.
- Restrict force-pushes — **except** the single coordinated window for the git
  history purge ([Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md) Part 5);
  re-enable the restriction immediately afterwards.
- Restrict pushes to `master` to maintainers.

---

## 5. SSH Preparation (Worker C)

### Key generation (operator workstation)

```bash
ssh-keygen -t ed25519 -C "vestra-deploy" -f ~/.ssh/vestra_deploy
```

Ed25519 is the recommended algorithm: short keys, fast verification, no
weak-parameter risk. RSA 4096 is an acceptable fallback for legacy tooling.

**Key storage:** the private key lives in the team password manager (or a
hardware token). It is never committed, never pasted into chat, never stored in
a `.txt` file in the repo — the `VPS.txt` incident (see §9) is exactly this
failure mode.

### Server user strategy

| User | Purpose | Login | sudo |
|---|---|---|---|
| `root` | Provisioning only | **Disabled** (`PermitRootLogin prohibit-password`, then `no` after `deploy` works) | — |
| `deploy` | Day-to-day operations, CI/CD deployments | SSH key only | Scoped: `docker compose`, `systemctl`, log reading — or full sudo with password if scoping proves impractical on a single-admin team |
| `administrator` (optional) | Break-glass emergency access | SSH key only, separate key pair | Full sudo |

`deploy` is added to the `docker` group (per the Deployment Guide §3) so the
GitHub Actions SSH step can run compose commands without sudo.

### SSH hardening (from Deployment Guide §3 + rotation checklist)

```bash
sudo sed -i 's/^#*PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
sudo sed -i 's/^#*PermitRootLogin.*/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config
sudo systemctl restart sshd

sudo ufw allow OpenSSH && sudo ufw allow 80/tcp && sudo ufw allow 443/tcp
sudo ufw enable
```

Recommended additions: `MaxAuthTries 3`, key-only auth for every account,
fail2ban on the SSH jail.

### Key installation & first login

1. From the provider console (web terminal): create `deploy`, paste the
   **public** key into `/home/deploy/.ssh/authorized_keys` (mode `600`,
   `.ssh` mode `700`, owned by `deploy`).
2. Test from the workstation **before** closing the console session:
   `ssh -i ~/.ssh/vestra_deploy deploy@<VPS_IP>`.
3. Only then disable password authentication and restart sshd.
4. Add the **private** key as the `SSH_KEY` GitHub Actions secret, with
   `SSH_HOST` / `SSH_USER`.

---

## 6. Third-Party Services (Worker D)

### Service inventory

| Service | Status | Required for |
|---|---|---|
| Flutterwave | ☐ Live account unverified | Payments — live public/secret/encryption keys, webhook secret |
| SMTP provider | ☐ Not selected/configured | Order confirmations, password resets, admin notifications |
| Domain email | ☐ Open decision | `MAIL_FROM_ADDRESS`, Let's Encrypt expiry notices (`ops@vestra.com` used in certbot commands) |
| Google services / Analytics | Not currently integrated | None found in the codebase — do not add scope pre-launch |

### Flutterwave — production checklist

- [ ] Live account approved (KYC/settlement complete) — test keys are **not** acceptable
- [ ] Live `FLUTTERWAVE_PUBLIC_KEY`, `FLUTTERWAVE_SECRET_KEY`, `FLUTTERWAVE_ENCRYPTION_KEY` from the dashboard
- [ ] Webhook configured in the dashboard pointing at
      `https://api.vestra.com/api/v1/payments/callback`
      (`backend/routes/api.php:105`, throttled via `throttle:webhook`)
- [ ] `FLUTTERWAVE_WEBHOOK_SECRET` set to the dashboard's **secret hash**, exactly —
      a mismatch rejects every webhook (`WebhookSecurityTest` covers this)

### SMTP — production checklist

- [ ] Provider selected (any of: domain email host, SendGrid, Mailgun, SES, …)
- [ ] `MAIL_HOST`, `MAIL_PORT` (587), `MAIL_USERNAME`, `MAIL_PASSWORD`
- [ ] `MAIL_FROM_ADDRESS` on a domain with valid SPF/DKIM (§3)
- [ ] Test send verified end-to-end after first boot (password-reset flow is a
      convenient probe)

### Where each credential is configured

| Credential | Lives in |
|---|---|
| Flutterwave keys + webhook secret | `/opt/vestra/.env.production` only |
| SMTP credentials | `/opt/vestra/.env.production` only |
| Docker registry credentials | GitHub Actions secrets only |
| Deployment SSH private key | GitHub secret `SSH_KEY` + operator password manager |
| `NEXT_PUBLIC_*` | GitHub Actions secrets (build args) **and** `.env.production` |

**Never in source control:** every value above, plus `APP_KEY`, database/Redis
passwords, and the bootstrap admin password. Only placeholder templates
(`.env.production.example`) are tracked. The
[Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md) exists precisely
because this rule was violated earlier in the project.

---

## 7. Production Secret Inventory (Worker E)

Generate **fresh** values for production — never reuse development values.
Minimum entropy: 256 bits (32 bytes) for generated passwords.

| Secret | Generation command | Storage | Rotation |
|---|---|---|---|
| `APP_KEY` | `docker run --rm php:8.4-cli php -r 'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'` (or `php artisan key:generate --show` in the container) | `.env.production` + **offline backup** | **Never after go-live** — encrypted-at-rest settings become undecryptable |
| `MYSQL_ROOT_PASSWORD` | `openssl rand -base64 32` | `.env.production` | On suspicion of exposure |
| `DB_PASSWORD` | `openssl rand -base64 32` | `.env.production` | On suspicion of exposure |
| `REDIS_PASSWORD` | `openssl rand -base64 32` | `.env.production` | On suspicion of exposure |
| `BOOTSTRAP_ADMIN_PASSWORD` | `openssl rand -base64 24` (must not be `Admin@12345` — the app refuses to boot) | `.env.production` + password manager | Immediately superseded: first admin login forces a password change |
| `FLUTTERWAVE_*` keys + webhook secret | Flutterwave dashboard (live) | `.env.production` | Via dashboard if compromised |
| `MAIL_PASSWORD` | SMTP provider | `.env.production` | Per provider policy |
| Session security | `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` are fixed in compose; entropy comes from `APP_KEY` | — | Tied to `APP_KEY` |
| JWT/API tokens | Sanctum — no static secret beyond `APP_KEY`; `SANCTUM_TOKEN_EXPIRATION` configured in compose | — | Token expiry, not rotation |
| `SSH_KEY`, `DOCKER_PASSWORD`, `SSH_HOST`, `SSH_USER`, `NEXT_PUBLIC_*` | See §4–§5 | GitHub Actions secrets | Annually or on staff change |

### Storage & handling rules

- Production secrets live **only** in `/opt/vestra/.env.production` — root-owned,
  mode `600`.
- `APP_KEY` additionally lives in an offline, durable backup (password manager).
  Losing it is unrecoverable for encrypted settings (§9).
- CI/CD secrets live only in GitHub Actions repository secrets.
- Full rotation procedure and sign-off:
  [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md).

---

## 8. Deployment Sequence (Worker F)

Dependencies flow top to bottom; do not start a step before its predecessors
complete. Detail for steps 4–11:
[Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md).

| # | Step | Depends on | Est. duration |
|---|---|---|---|
| 1 | Close Critical/High outstanding actions (§10) — domain confirmed, credentials rotated, Flutterwave live approved, SMTP selected | Owner action | 1–5 days (external lead time) |
| 2 | **Purchase VPS** — Ubuntu 22.04 LTS or Debian 12, ≥2 vCPU, ≥4 GB RAM (8 GB recommended), ≥40 GB SSD | Step 1 (domain decision) | 1 hour |
| 3 | Provision & harden — `deploy` user, SSH keys, sshd hardening, ufw (22/80/443) | Step 2 | 1 hour |
| 4 | Install Docker Engine 24+ and Compose 2.20+ | Step 3 | 30 min |
| 5 | **DNS records** (`@`, `www`, `api` → VPS IP) and propagation check with `dig` | Steps 2, domain access | 5 min – 24 h (start early) |
| 6 | Clone repo to `/opt/vestra`; create `.env.production` (`chmod 600`); generate all secrets (§7); `docker compose … config --quiet` passes | Steps 3–4 | 1 hour |
| 7 | TLS — standalone certbot issuance for `vestra.com` + `api.vestra.com` (use `--dry-run` while testing; 5 duplicate certs/week limit) | Step 5 | 30 min |
| 8 | First launch — `up -d --build`, all 8 services healthy, stage product images, one-time `db:seed --force`, `media:validate` | Steps 6–7 | 1–2 h (90s+ first boot) |
| 9 | Bootstrap admin — log in at `/admin`, complete forced password change | Step 8 | 15 min |
| 10 | Configure GitHub Actions secrets (`SSH_HOST/USER/KEY`) and verify the pipeline deploys | Steps 3–4 | 30 min |
| 11 | **DNS verification & smoke tests** — health endpoints, HTTPS redirect, HSTS, CORS, product images, **full purchase journey with a live webhook** ([Deployment Verification Checklist](DEPLOYMENT_VERIFICATION_CHECKLIST.md)) | Steps 8–9 | 2 h |
| 12 | Operations — nightly backup cron, first backup **taken and verified**, off-site replication, uptime monitoring on `/api/v1/health`, rollback rehearsal | Step 8 | 2 h |
| 13 | **Soft launch** — [Go-Live Checklist](GO_LIVE_CHECKLIST.md) phases 6–8, sign-off, first-24-hours monitoring | Steps 1–12 | 1 day |

**Estimated total:** ~1.5–2 working days of hands-on work plus DNS propagation
and any external-account lead time (Flutterwave KYC is the long pole — start it
first).

### Rollback preparation

- Every automated deploy runs `backup.sh` first and records `PREVIOUS_TAG`;
  `rollback.sh` restores the previous image tag in one command.
- Rollback reverts **code, not the database** — destructive migrations require a
  backup restore instead. See [Rollback Checklist](ROLLBACK_CHECKLIST.md).
- Rehearse one rollback before soft launch (Go-Live Checklist Phase 7).
- Abort criteria (payments failing/double-charging, auth broken, 5xx > 5%) are
  defined in the Go-Live Checklist.

---

## 9. Risk Assessment (Worker G)

| # | Risk | Class | Mitigation |
|---|---|---|---|
| R1 | **Domain ownership/registrar/DNS access unconfirmed.** Without the domain there is no DNS, no TLS, no launch. | **Critical** | Owner confirms registration + access this week (§2); substitute the real domain everywhere if not `vestra.com` |
| R2 | **Exposed credentials still live in git history** (`VPS.txt` — VPS root password; `New Text Document.txt` — admin password + a value labelled `AWS`). Anyone who ever cloned retains them. | **Critical** | Execute [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md) Parts 1–3 **before** go-live (mandatory per Phase 15): rotate all three, delete the AWS key in IAM, audit auth logs. History purge (Part 5) strongly recommended |
| R3 | **Flutterwave live account not verified.** KYC/settlement approval has external lead time; test keys in production would silently fail or misroute money. | **High** | Begin live onboarding immediately; confirm dashboard shows live keys before step 11 smoke tests |
| R4 | **SMTP/sender not configured.** No order confirmations or password resets; users lock themselves out. | **High** | Select provider in step 1; verify a real send during smoke tests |
| R5 | **`APP_KEY` loss.** Encrypted-at-rest settings become permanently undecryptable. | **High** | Offline durable backup at generation time; treat as crown-jewel (§7) |
| R6 | **Single VPS = single point of failure; backups initially local-only.** Hardware failure takes the site and its backups together. | Medium | Off-site backup replication in step 12 (Go-Live Phase 7); rehearse restore on a non-production host |
| R7 | **`NEXT_PUBLIC_*` build-time trap.** Changing the API domain post-build does nothing until the frontend image is rebuilt. | Medium | Documented in DEPLOYMENT.md; values set once, correctly, in GitHub secrets before the first build |
| R8 | **Let's Encrypt rate limits** (5 duplicate certs/week) if issuance is retried carelessly during testing. | Low | Always `--dry-run` first (Deployment Guide §5) |
| R9 | **DNS proxied through a CDN mode** breaking client-IP visibility (rate limits, audit logs) and TLS. | Low | Keep records DNS-only at launch; revisit `TRUSTED_PROXIES` before any proxying |
| R10 | **Missing `PREVIOUS_TAG`** leaves rollback without a target on the very first deploy. | Low | Set manually on first deploy; automated thereafter |

---

## 10. Outstanding Actions

Blocking items must close before the corresponding deployment step (§8).

### Critical — before go-live (external, owner action)

- [ ] Confirm production domain registration, registrar access and DNS
      management access; confirm the domain is `vestra.com` or substitute the
      real one (§2)
- [ ] Rotate the three exposed credentials: VPS root password,
      `admin@vestra.com` password, AWS key (deactivate **and delete** in IAM,
      audit CloudTrail) — [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md) Part 1
- [ ] Decide and schedule the git history purge (Part 5); re-clone all working
      copies afterwards

### High — before smoke testing

- [ ] Complete Flutterwave live onboarding; obtain live keys; configure the
      webhook URL and secret hash (§6)
- [ ] Select and configure an SMTP provider and sender address (§6)
- [ ] Decide whether domain email is hosted on this domain; create MX/SPF/DKIM/
      DMARC records if so (§3)

### Medium — after VPS purchase (step-dependent)

- [ ] Create GitHub Actions secrets: `SSH_HOST`, `SSH_USER`, `SSH_KEY`
      (post-provisioning); `DOCKER_USERNAME`, `DOCKER_PASSWORD`, `NEXT_PUBLIC_*`
      can be set now (§4)
- [ ] Enable branch protection on `master` (§4)
- [ ] Configure off-site backup replication and uptime monitoring (step 12)

---

## 11. Final Recommendation

### Coordinator Certification

> **🟡 READY WITH MINOR PREPARATION**

All prerequisites for production deployment have been **identified and
documented**. The application, infrastructure definitions, CI/CD pipeline and
operational runbooks are complete and verified in the repository.

**VPS purchase may proceed now** — nothing required at purchase time is missing;
the server IP is only consumed by steps that come after purchase (DNS records,
GitHub secrets).

**Go-live remains 🔴 gated** until the Critical outstanding actions close:

1. Domain ownership and DNS access confirmed (R1)
2. Exposed credentials rotated and the AWS key deleted (R2)

These are external account/credential actions, not engineering work. Once they
are signed off in §10, the status advances to **🟢 READY TO PURCHASE VPS →
DEPLOY**, and the deployment sequence in §8 can be executed end-to-end with no
unknown prerequisites remaining.

### Acceptance criteria status

| Criterion | Status |
|---|---|
| Domain ownership confirmed | ☐ Outstanding action (R1) — plan and records fully documented |
| DNS plan completed | ✅ §3 |
| Repository access verified | ✅ §4 (`github.com/mujuzifil/Vestra`, `master`) |
| SSH preparation completed | ✅ §5 |
| Third-party service requirements documented | ✅ §6 |
| Production secret inventory completed | ✅ §7 |
| Deployment sequence documented | ✅ §8 |
| Risks identified | ✅ §9 |
| No unknown prerequisites remain | ✅ — every external dependency is listed in §10 |
