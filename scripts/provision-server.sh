#!/bin/bash
# ==============================================================================
# VESTRA — Stage 17.2: Production server provisioning & hardening
# ==============================================================================
# Usage (run as root on the VPS):
#
#   ./provision-server.sh                        # phase 1: everything except SSH lockdown
#   HARDEN_SSH_CONFIRM=yes ./provision-server.sh # phase 2: apply SSH lockdown
#
# Optional environment:
#   DEPLOY_SSH_PUBLIC_KEY="<pubkey>"   install this key for the deploy user
#                                      (default: copy root's authorized_keys)
#   SET_HOSTNAME="vestra-prod"         set the system hostname
#
# Idempotent: safe to re-run. Every step checks state before changing it.
#
# Scope: OS preparation, user/SSH hardening, UFW, Fail2Ban, unattended-upgrades,
# system optimisation. This script deliberately does NOT install Docker, touch
# DNS, or configure TLS — those are Stages 17.3/17.4.
# ==============================================================================
set -euo pipefail

log()  { echo -e "\033[0;34m[provision]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[provision]\033[0m $*"; }
warn() { echo -e "\033[0;33m[provision]\033[0m $*"; }
fail() { echo -e "\033[0;31m[provision]\033[0m $*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || fail "Must run as root."

export DEBIAN_FRONTEND=noninteractive

# ------------------------------------------------------------------------------
# Worker A — Operating system preparation
# ------------------------------------------------------------------------------
log "Verifying operating system..."
. /etc/os-release
[ "${ID:-}" = "ubuntu" ] || fail "Expected Ubuntu, found '${ID:-unknown}'."
[ "${VERSION_ID:-}" = "24.04" ] || warn "Expected Ubuntu 24.04, found ${VERSION_ID:-unknown} — continuing."
ok "OS: ${PRETTY_NAME:-unknown} | kernel: $(uname -r)"

log "Updating package repositories and upgrading installed packages..."
apt-get update -qq
apt-get upgrade -y -qq
apt-get autoremove --purge -y -qq
ok "System fully updated; obsolete packages removed."

log "Installing essential administration tools..."
apt-get install -y -qq \
    git curl wget unzip zip htop jq tree nano vim net-tools dnsutils \
    software-properties-common ca-certificates gnupg lsb-release
ok "Essential tools installed."

log "Configuring timezone and time synchronization..."
timedatectl set-timezone UTC
systemctl enable --now systemd-timesyncd
timedatectl set-ntp true
ok "Timezone: $(timedatectl show -p Timezone --value) | NTP: $(timedatectl show -p NTPSynchronized --value 2>/dev/null || echo pending)"

log "System overview:"
echo "  Disk:   $(df -h / | awk 'NR==2 {print $3" used / "$2" total ("$5")"}')"
echo "  Memory: $(free -h | awk '/^Mem:/ {print $3" used / "$2" total"}')"
echo "  CPU:    $(nproc) vCPU — $(grep -m1 'model name' /proc/cpuinfo | cut -d: -f2- | xargs)"

# ------------------------------------------------------------------------------
# Worker B — deploy user & SSH key installation
# ------------------------------------------------------------------------------
log "Configuring deploy user..."
if id deploy &>/dev/null; then
    ok "User 'deploy' already exists."
else
    useradd -m -s /bin/bash deploy
    ok "User 'deploy' created."
fi
usermod -aG sudo deploy

DEPLOY_HOME="$(getent passwd deploy | cut -d: -f6)"
install -d -m 700 -o deploy -g deploy "$DEPLOY_HOME/.ssh"

if [ -n "${DEPLOY_SSH_PUBLIC_KEY:-}" ]; then
    grep -qF "$DEPLOY_SSH_PUBLIC_KEY" "$DEPLOY_HOME/.ssh/authorized_keys" 2>/dev/null \
        || echo "$DEPLOY_SSH_PUBLIC_KEY" >> "$DEPLOY_HOME/.ssh/authorized_keys"
    ok "Installed key from DEPLOY_SSH_PUBLIC_KEY."
elif [ -s /root/.ssh/authorized_keys ]; then
    # Hostinger provisioned the box with the operator's key on root — reuse it
    # so access is never lost. Merge without duplicating lines.
    touch "$DEPLOY_HOME/.ssh/authorized_keys"
    while IFS= read -r key; do
        [ -n "$key" ] || continue
        grep -qF "$key" "$DEPLOY_HOME/.ssh/authorized_keys" || echo "$key" >> "$DEPLOY_HOME/.ssh/authorized_keys"
    done < /root/.ssh/authorized_keys
    ok "Copied root's authorized_keys to deploy."
else
    warn "No key source found — deploy has NO authorized_keys. Set DEPLOY_SSH_PUBLIC_KEY and re-run before hardening SSH."
fi
chown deploy:deploy "$DEPLOY_HOME/.ssh/authorized_keys" 2>/dev/null || true
chmod 600 "$DEPLOY_HOME/.ssh/authorized_keys" 2>/dev/null || true

# ------------------------------------------------------------------------------
# Worker B (phase 2) — SSH hardening, gated on explicit confirmation
# ------------------------------------------------------------------------------
SSHD_DROPIN="/etc/ssh/sshd_config.d/99-vestra-hardening.conf"
if [ "${HARDEN_SSH_CONFIRM:-}" = "yes" ]; then
    [ -s "$DEPLOY_HOME/.ssh/authorized_keys" ] \
        || fail "Refusing to harden SSH: $DEPLOY_HOME/.ssh/authorized_keys is empty. You would lock yourself out."
    log "Applying SSH hardening drop-in..."
    cat > "$SSHD_DROPIN" <<'EOF'
# VESTRA Stage 17.2 — SSH hardening. Managed by scripts/provision-server.sh.
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
PermitEmptyPasswords no
MaxAuthTries 3
ClientAliveInterval 300
ClientAliveCountMax 2
EOF
    sshd -t || fail "sshd configuration test failed — drop-in left in place for inspection, sshd NOT reloaded."
    systemctl reload ssh
    ok "SSH hardened (root login off, password auth off, MaxAuthTries 3, keepalive 300x2) and sshd reloaded."
else
    warn "SSH lockdown SKIPPED. Verify 'ssh deploy@<ip>' works in a NEW session, then re-run with HARDEN_SSH_CONFIRM=yes."
fi

# ------------------------------------------------------------------------------
# Worker C — Firewall (UFW)
# ------------------------------------------------------------------------------
log "Configuring UFW firewall..."
apt-get install -y -qq ufw
ufw default deny incoming >/dev/null
ufw default allow outgoing >/dev/null
ufw allow 22/tcp  >/dev/null
ufw allow 80/tcp  >/dev/null
ufw allow 443/tcp >/dev/null
grep -q '^IPV6=yes' /etc/default/ufw || sed -i 's/^IPV6=.*/IPV6=yes/' /etc/default/ufw
ufw --force enable >/dev/null
ok "UFW enabled: allow 22/80/443 (v4+v6), deny all other inbound."

# ------------------------------------------------------------------------------
# Worker D — Intrusion prevention (Fail2Ban)
# ------------------------------------------------------------------------------
log "Configuring Fail2Ban..."
apt-get install -y -qq fail2ban
cat > /etc/fail2ban/jail.d/vestra-sshd.conf <<'EOF'
# VESTRA Stage 17.2 — SSH jail. Managed by scripts/provision-server.sh.
[sshd]
enabled  = true
backend  = systemd
maxretry = 5
findtime = 10m
bantime  = 1h
EOF
systemctl enable --now fail2ban
systemctl restart fail2ban
ok "Fail2Ban running (sshd jail: 5 retries / 10m window / 1h ban)."

# ------------------------------------------------------------------------------
# Worker E — Automatic security updates
# ------------------------------------------------------------------------------
log "Configuring unattended-upgrades..."
apt-get install -y -qq unattended-upgrades
cat > /etc/apt/apt.conf.d/20auto-upgrades <<'EOF'
// VESTRA Stage 17.2 — automatic security updates. Managed by scripts/provision-server.sh.
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
APT::Periodic::AutocleanInterval "7";
EOF
# Ensure security origin, unused-dependency cleanup, and NO automatic reboot
# (reboots on a production host are a deliberate, announced operation — the
# motd notice flags when one is required).
sed -i 's|^//[[:space:]]*"\${distro_id}:\${distro_codename}-security";|        "${distro_id}:${distro_codename}-security";|' \
    /etc/apt/apt.conf.d/50unattended-upgrades
sed -i 's|^//Unattended-Upgrade::Remove-Unused-Dependencies.*|Unattended-Upgrade::Remove-Unused-Dependencies "true";|' \
    /etc/apt/apt.conf.d/50unattended-upgrades
sed -i 's|^//Unattended-Upgrade::Automatic-Reboot .*|Unattended-Upgrade::Automatic-Reboot "false";|' \
    /etc/apt/apt.conf.d/50unattended-upgrades
ok "unattended-upgrades enabled (security updates, auto-cleanup, no auto-reboot)."

# ------------------------------------------------------------------------------
# Worker F — System optimisation (Docker host defaults; Docker NOT installed)
# ------------------------------------------------------------------------------
log "Applying system optimisations..."
cat > /etc/sysctl.d/99-vestra.conf <<'EOF'
# VESTRA Stage 17.2 — managed by scripts/provision-server.sh.
# Prefer keeping hot pages in RAM over swapping; Redis and MySQL will live here.
vm.swappiness = 10
EOF
sysctl --system >/dev/null

cat > /etc/security/limits.d/99-vestra.conf <<'EOF'
# VESTRA Stage 17.2 — managed by scripts/provision-server.sh.
# Headroom for nginx, PHP-FPM and Node under load.
* soft nofile 65535
* hard nofile 65535
EOF

install -d /etc/systemd/journald.conf.d
cat > /etc/systemd/journald.conf.d/99-vestra.conf <<'EOF'
# VESTRA Stage 17.2 — managed by scripts/provision-server.sh.
[Journal]
SystemMaxUse=500M
MaxRetentionSec=1month
EOF
systemctl restart systemd-journald
ok "Swappiness=10, nofile=65535, journald capped at 500M/1month."

if [ -n "${SET_HOSTNAME:-}" ]; then
    hostnamectl set-hostname "$SET_HOSTNAME"
    grep -q " $SET_HOSTNAME" /etc/hosts || echo "127.0.1.1 $SET_HOSTNAME" >> /etc/hosts
    ok "Hostname set to '$SET_HOSTNAME'."
fi

echo
ok "Provisioning complete."
[ "${HARDEN_SSH_CONFIRM:-}" = "yes" ] \
    || warn "REMINDER: SSH lockdown pending. Verify deploy login in a NEW session, then re-run with HARDEN_SSH_CONFIRM=yes."
log "Next: run scripts/validate-server.sh and record results in docs/release/STAGE_17_2_SERVER_PROVISIONING_AND_HARDENING.md §10."
