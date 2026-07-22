# VESTRA — Secret Rotation Checklist

**Status: 🔴 MANDATORY BEFORE GO-LIVE**
**Raised:** Phase 15 (2026-07-22)

---

## Why this exists

Two files containing plaintext credentials were committed to this repository and
tracked in git history:

| File | Contents |
|------|----------|
| `VPS.txt` | Production VPS root password |
| `New Text Document.txt` | `admin@vestra.com` admin password, a value labelled `AWS` |

Both have been removed from the working tree and the index in Phase 15. **They
remain in git history.**

Treat every credential listed below as **compromised**. Anyone who has ever
cloned, forked, or pulled this repository — including CI runners and any mirror
— retains a copy. Removing a file from `HEAD` does not unpublish it.

Rotation is not optional and is not satisfied by the history purge alone.

---

## Part 1 — Rotate exposed credentials (do this first)

Rotate these **before** the history rewrite. The rewrite is slow and rotation is
what actually closes the exposure.

- [ ] **VPS root password** — change via provider console. Then disable password
      authentication entirely and move to SSH keys:
      `PermitRootLogin prohibit-password` and `PasswordAuthentication no` in
      `/etc/ssh/sshd_config`, then `systemctl restart sshd`.
- [ ] **`admin@vestra.com` administrator password** — the exposed value was
      `Admin@12345`. Rotate to a strong unique value. Verify the old password no
      longer authenticates at `/admin`.
- [ ] **AWS credential** (the value labelled `AWS`) — deactivate then delete the
      access key in IAM. Do not merely deactivate. Audit CloudTrail for use of
      that key ID before deletion.
- [ ] Review the audit log for unexpected admin sessions:
      `SELECT * FROM audit_logs WHERE action LIKE 'login%' ORDER BY created_at DESC;`
- [ ] Review VPS auth history for unexpected root logins:
      `last -f /var/log/wtmp` and `grep -i "accepted" /var/log/auth.log`

## Part 2 — Generate fresh production secrets

These were never exposed, but production must not reuse any development value.
Generate all of them fresh when populating `.env.production`.

- [ ] `APP_KEY` — `docker compose -f docker-compose.prod.yml run --rm backend php artisan key:generate --show`
- [ ] `MYSQL_ROOT_PASSWORD` — `openssl rand -base64 32`
- [ ] `DB_PASSWORD` — `openssl rand -base64 32`
- [ ] `REDIS_PASSWORD` — `openssl rand -base64 32`
- [ ] `BOOTSTRAP_ADMIN_PASSWORD` — strong unique value; the application refuses
      to boot in production while the default is in use
      (`ProductionBootstrapPasswordTest` enforces this).

## Part 3 — Third-party credentials

- [ ] `FLUTTERWAVE_PUBLIC_KEY` — live key from the Flutterwave dashboard
- [ ] `FLUTTERWAVE_SECRET_KEY` — live key
- [ ] `FLUTTERWAVE_ENCRYPTION_KEY` — live key
- [ ] `FLUTTERWAVE_WEBHOOK_SECRET` — must match the secret hash configured in the
      Flutterwave dashboard, or every webhook is rejected
      (`WebhookSecurityTest` covers signature rejection)
- [ ] `MAIL_PASSWORD` — SMTP credential
- [ ] `DOCKER_USERNAME` / `DOCKER_PASSWORD` — GitHub Actions secrets
- [ ] `SSH_KEY` / `SSH_HOST` / `SSH_USER` — GitHub Actions deployment secrets

## Part 4 — Storage rules

- [ ] Production secrets live **only** in `/opt/vestra/.env.production` on the
      VPS, owned by root, mode `600`.
- [ ] Nothing in `.env.production` is ever committed. Only
      `.env.production.example` — placeholders only — is tracked.
- [ ] CI/CD secrets live in GitHub Actions repository secrets.
- [ ] Never paste credentials into `.txt` files in the repo, issue comments, or
      chat transcripts.

---

## Part 5 — Git history purge (manual — run this yourself)

**This was deliberately not automated.** It rewrites every commit SHA on
`master` and requires a force-push. It must be a conscious decision, coordinated
with anyone else holding a clone.

Rotate the credentials (Parts 1–3) first. If you only have one option, rotate —
the purge without rotation protects nothing.

```bash
# 1. Back up the repository first — this is destructive and irreversible.
git clone --mirror . ../vestra-backup.git

# 2. Install git-filter-repo (preferred over filter-branch).
pip install git-filter-repo

# 3. Purge both files from every commit.
git filter-repo --invert-paths \
    --path "VPS.txt" \
    --path "New Text Document.txt" \
    --force

# 4. Verify they are gone from all history.
git log --all --oneline -- "VPS.txt" "New Text Document.txt"   # expect no output

# 5. Re-add the remote (filter-repo strips it) and force-push.
git remote add origin <your-remote-url>
git push origin --force --all
git push origin --force --tags
```

### After the rewrite

- [ ] Every collaborator re-clones. Old clones **cannot** be merged back — doing
      so reintroduces the secrets.
- [ ] Delete stale forks and mirrors.
- [ ] If the repository is or ever was public, contact GitHub Support to purge
      cached views of the affected blobs. Cached blob URLs stay reachable after
      a force-push until GitHub garbage-collects them.
- [ ] Delete `../vestra-backup.git` once the rewrite is confirmed good — it still
      contains the secrets.

---

## Sign-off

| Item | Completed by | Date |
|------|--------------|------|
| VPS root password rotated | | |
| Admin password rotated | | |
| AWS key deleted | | |
| Production secrets generated | | |
| Flutterwave live keys set | | |
| Git history purged | | |
| Collaborators re-cloned | | |

Go-live is blocked until Parts 1–3 are signed off.
