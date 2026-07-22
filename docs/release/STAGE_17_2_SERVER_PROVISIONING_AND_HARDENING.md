# Stage 17.2 — Production Server Provisioning & Hardening

**Status:** 🟡 HARDENED WITH OBSERVATIONS — procedures complete and scripted;
**live validation output pending operator execution on the VPS**
**Date:** 2026-07-22
**Scope:** Operating system preparation and hardening only. No Docker, no
application deployment, no DNS, no TLS.

**Related:** [Stage 16 — Deployment Preparation](STAGE_16_PRODUCTION_DEPLOYMENT_PREPARATION.md) ·
[Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md) ·
[Go-Live Checklist](GO_LIVE_CHECKLIST.md)

---

## 1. Executive Summary

The production VPS (Hostinger, Ubuntu Server 24.04 LTS, SSH-key authentication
verified at provisioning) was prepared for hardening. Every action in this stage
is implemented as an **idempotent, re-runnable script** so the result is
documented, repeatable and auditable rather than a sequence of one-off shell
commands:

| Artifact | Purpose |
|---|---|
| `scripts/provision-server.sh` | Applies all hardening (Workers A–F). Idempotent; safe to re-run |
| `scripts/validate-server.sh` | Read-only validation of every acceptance criterion (Worker G); prints PASS/FAIL and exits non-zero on failure |

**Execution model.** The scripts run on the VPS as root. SSH lockdown is split
behind an explicit confirmation gate so the operator cannot lock themselves out
(§5). Live execution output belongs in §10 — it is produced by running
`validate-server.sh` on the server, not written by hand.

**Observation (why 🟡 and not 🟢):** the provisioning/validation tooling and
this report are complete and statically verified, but the Coordinator can only
certify 🟢 PRODUCTION SERVER HARDENED once §10 contains real PASS output from
the server. That requires an operator SSH session — a 15-minute task (§12).

## 2. Server Specifications

| Item | Value |
|---|---|
| Provider | Hostinger VPS |
| OS | Ubuntu Server 24.04 LTS (asserted by the provisioning script) |
| Authentication | SSH key only (verified at provisioning) |
| Public IP | Assigned (recorded in the team password manager, **not** in this repo) |
| Kernel / CPU / Memory / Disk | Filled from `validate-server.sh` output in §10 |
| Timezone | UTC (configured in this stage) |
| Hostname | Optional — set with `SET_HOSTNAME=vestra-prod` |

## 3. Installed Packages

Installed via `apt-get` after a full `update`/`upgrade`/`autoremove --purge`:

| Package | Purpose |
|---|---|
| `git` | Repository clone to `/opt/vestra` (Stage 17.5) |
| `curl`, `wget` | Health checks, downloads, deployment scripts |
| `unzip`, `zip` | Archive handling |
| `htop` | Interactive resource monitoring |
| `jq` | JSON inspection (health endpoint, compose) |
| `tree` | Directory inspection |
| `nano`, `vim` | On-server editing |
| `net-tools` | Legacy network diagnostics |
| `dnsutils` | `dig` for DNS verification (Stage 17.4) |
| `software-properties-common` | Repository management |
| `ca-certificates` | TLS trust store |
| `gnupg` | Apt key verification |
| `lsb-release` | OS identification |
| `ufw` | Firewall (§6) |
| `fail2ban` | Intrusion prevention (§7) |
| `unattended-upgrades` | Automatic security updates (§8) |

## 4. User Configuration

| Item | Configuration |
|---|---|
| User | `deploy` — created with home directory, `/bin/bash` shell |
| Privilege | Member of `sudo` group |
| SSH directory | `/home/deploy/.ssh`, mode `700`, owned `deploy:deploy` |
| Authorized keys | `/home/deploy/.ssh/authorized_keys`, mode `600`, owned `deploy:deploy` |
| Key source | `DEPLOY_SSH_PUBLIC_KEY` env var if provided; otherwise root's existing `authorized_keys` is merged in (Hostinger provisions the operator key onto root) so access is never lost |

The `docker` group grant is deliberately deferred to Stage 17.3 — the group
does not exist until Docker is installed.

## 5. SSH Hardening

Applied as a drop-in — `/etc/ssh/sshd_config.d/99-vestra-hardening.conf` — which
leaves the stock `sshd_config` untouched and makes the change trivially
reversible:

```
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
PermitEmptyPasswords no
MaxAuthTries 3
ClientAliveInterval 300
ClientAliveCountMax 2
```

**Verify-before-lockdown gate.** The drop-in is written only when the script is
re-run with `HARDEN_SSH_CONFIRM=yes`, and even then the script refuses to
proceed if `deploy`'s `authorized_keys` is empty. Between the two runs the
operator must prove key-based access works in a **new** session:

```bash
ssh deploy@<VPS_IP> 'sudo whoami'    # must print: root
```

The config is validated with `sshd -t` before `systemctl reload ssh`; a failed
test aborts without reloading. Reload (not restart) keeps the existing session
alive — Ubuntu 24.04 runs sshd under socket activation, so new connections pick
up the new config immediately.

## 6. Firewall Configuration

UFW, persistent across reboots, IPv6 enabled (`IPV6=yes` in `/etc/default/ufw`):

| Rule | Policy |
|---|---|
| Default incoming | **deny** |
| Default outgoing | allow |
| `22/tcp` | ALLOW (SSH) |
| `80/tcp` | ALLOW (HTTP → redirect + ACME challenge, Stage 17.6) |
| `443/tcp` | ALLOW (HTTPS) |

Ports 3000/8080 (frontend/backend) are never opened at the host firewall — in
the target architecture they are reachable only on the internal Docker network
([Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md) §1).

## 7. Fail2Ban Configuration

Drop-in `/etc/fail2ban/jail.d/vestra-sshd.conf`, service enabled at boot:

| Parameter | Value | Rationale |
|---|---|---|
| `backend` | `systemd` | Ubuntu 24.04 logs sshd to the journal, not `/var/log/auth.log` |
| `maxretry` | 5 | Tolerates operator typos, stops scripted guessing |
| `findtime` | 10m | Window in which retries are counted |
| `bantime` | 1h | Long enough to break credential-stuffing scripts |

Validated via `fail2ban-client status sshd`.

## 8. Automatic Updates

`unattended-upgrades` with:

- Security updates enabled (`${distro_id}:${distro_codename}-security` origin)
- Package lists refreshed daily (`APT::Periodic::Update-Package-Lists "1"`)
- Weekly cache cleanup and unused-dependency removal
- **`Automatic-Reboot "false"`** — a production host reboots deliberately,
  during an announced window. When a kernel update needs a reboot, the motd
  notice (`*** System restart required ***`) flags it for the operator.

Timers verified: `apt-daily.timer`, `apt-daily-upgrade.timer`.

## 9. System Optimisations

Docker-host-appropriate defaults (Docker itself is Stage 17.3):

| Setting | Value | Location |
|---|---|---|
| `vm.swappiness` | `10` | `/etc/sysctl.d/99-vestra.conf` — keep Redis/MySQL pages in RAM |
| `nofile` soft/hard | `65535` | `/etc/security/limits.d/99-vestra.conf` — headroom for nginx/PHP-FPM/Node |
| Journal cap | `SystemMaxUse=500M`, `MaxRetentionSec=1month` | `/etc/systemd/journald.conf.d/99-vestra.conf` — bounded disk usage |
| Timezone | UTC | `timedatectl` — log correlation across services |
| Time sync | `systemd-timesyncd`, NTP enabled | certificate validity, log ordering |
| Hostname | optional via `SET_HOSTNAME` | `/etc/hosts` entry added when set |

## 10. Validation Results

**Pending — paste the full output of `validate-server.sh` here after execution.**

Run on the VPS as root:

```bash
/root/validate-server.sh | tee /root/stage-17-2-validation.txt
```

Expected result (every line PASS, exit code 0):

- OS is Ubuntu 24.04 LTS; timezone UTC; NTP synchronized
- `deploy` exists, in `sudo`, `.ssh` 700 / `authorized_keys` 600 / `deploy:deploy`, non-empty
- `sshd -T`: `permitrootlogin no`, `passwordauthentication no`,
  `kbdinteractiveauthentication no`, `permitemptypasswords no`,
  `maxauthtries 3`, `clientaliveinterval 300`, `clientalivecountmax 2`
- UFW active; 22/80/443 allowed; IPv6 rules present
- Fail2Ban active, enabled at boot, `sshd` jail active
- unattended-upgrades enabled; `apt-daily` timers active; auto-reboot `false`
- `vm.swappiness = 10`; nofile 65535; journald running
- Only port 22 listening on the host

```
<paste validation output here>
```

## 11. Security Recommendations

Beyond this stage's scope, before or during later stages:

- **Provider-level firewall** (Hostinger panel) as a second layer in front of
  UFW — defense in depth costs nothing here.
- **Back up the `deploy` private key** to the team password manager; it is the
  only way onto the box once password auth is off.
- **Reboot policy:** schedule a monthly maintenance window for kernel updates
  flagged by the motd notice — auto-reboot is deliberately off (§8).
- **Root password:** rotate/invalidate the provider-issued root password
  (`passwd -l root` after `deploy` sudo is confirmed) — SSH root login is off,
  but the password still works on the provider console.
- **Monitoring:** uptime checks land in the deployment stage (Go-Live Checklist
  Phase 7); consider the provider's VPS metrics alerts for CPU/disk.
- **Never store server credentials in the repo** — the `VPS.txt` incident
  ([Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md)) is the standing
  reminder.

## 12. Outstanding Actions

- [ ] Copy scripts to the VPS:
      `scp scripts/provision-server.sh scripts/validate-server.sh root@<VPS_IP>:/root/`
- [ ] Run phase 1: `/root/provision-server.sh`
- [ ] In a **new** session, verify `ssh deploy@<VPS_IP> 'sudo whoami'` prints `root`
- [ ] Run phase 2: `HARDEN_SSH_CONFIRM=yes /root/provision-server.sh`
- [ ] Run `/root/validate-server.sh` — expect all PASS, exit 0
- [ ] Paste validation output into §10 and flip this document's status
- [ ] Lock the provider-issued root password (`passwd -l root`) once `deploy`
      sudo is confirmed
- [ ] Commit these scripts to the repository

**Gate:** Stage 17.3 (Docker Platform Installation) begins only after
`validate-server.sh` reports **0 failed**.

---

## Coordinator Certification

> **🟡 HARDENED WITH OBSERVATIONS**

The hardening procedure is complete, idempotent, documented, and statically
verified. The single observation is that live execution evidence (§10) is
collected by the operator on the VPS. When `validate-server.sh` reports 0
failures, this stage advances to **🟢 PRODUCTION SERVER HARDENED** and Stage
17.3 may begin.
