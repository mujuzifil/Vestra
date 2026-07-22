#!/bin/bash
# ==============================================================================
# VESTRA — Stage 17.2: Server hardening validation
# ==============================================================================
# Usage (run as root on the VPS, after provision-server.sh):
#
#   ./validate-server.sh
#
# Read-only. Prints PASS/FAIL per check plus a summary; exits non-zero if any
# check fails. Paste the full output into
# docs/release/STAGE_17_2_SERVER_PROVISIONING_AND_HARDENING.md §10.
# ==============================================================================
set -uo pipefail

PASS=0
FAIL=0

pass() { echo "PASS  $*"; PASS=$((PASS + 1)); }
fail() { echo "FAIL  $*"; FAIL=$((FAIL + 1)); }
info() { echo "      $*"; }

check() { # check <label> <command...>
    local label="$1"; shift
    if "$@" >/dev/null 2>&1; then pass "$label"; else fail "$label"; fi
}

echo "=============================================================================="
echo "VESTRA Stage 17.2 — Validation"
echo "Host: $(hostname) | $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
echo "=============================================================================="

# --- Worker A: OS & resources -------------------------------------------------
. /etc/os-release
[ "${ID:-}" = "ubuntu" ] && [ "${VERSION_ID:-}" = "24.04" ] \
    && pass "OS is Ubuntu 24.04 LTS (${PRETTY_NAME})" \
    || fail "OS is not Ubuntu 24.04 (${PRETTY_NAME:-unknown})"
info "Kernel: $(uname -r)"
info "Disk:   $(df -h / | awk 'NR==2 {print $3" used / "$2" total ("$5")"}')"
info "Memory: $(free -h | awk '/^Mem:/ {print $3" used / "$2" total"}')"
info "CPU:    $(nproc) vCPU"

[ "$(timedatectl show -p Timezone --value)" = "UTC" ] \
    && pass "Timezone is UTC" \
    || fail "Timezone is not UTC ($(timedatectl show -p Timezone --value))"
[ "$(timedatectl show -p NTPSynchronized --value)" = "yes" ] \
    && pass "Time synchronized (systemd-timesyncd)" \
    || fail "Time not synchronized (timedatectl: NTPSynchronized=no)"

# --- Worker B: deploy user ----------------------------------------------------
check "deploy user exists" id deploy
id deploy 2>/dev/null | grep -q '(sudo)' \
    && pass "deploy is in the sudo group" \
    || fail "deploy is NOT in the sudo group"
[ "$(stat -c '%a' /home/deploy/.ssh 2>/dev/null)" = "700" ] \
    && pass "/home/deploy/.ssh mode 700" \
    || fail "/home/deploy/.ssh mode is $(stat -c '%a' /home/deploy/.ssh 2>/dev/null || echo missing)"
[ "$(stat -c '%a' /home/deploy/.ssh/authorized_keys 2>/dev/null)" = "600" ] \
    && pass "authorized_keys mode 600" \
    || fail "authorized_keys mode is $(stat -c '%a' /home/deploy/.ssh/authorized_keys 2>/dev/null || echo missing)"
[ "$(stat -c '%U:%G' /home/deploy/.ssh/authorized_keys 2>/dev/null)" = "deploy:deploy" ] \
    && pass "authorized_keys owned by deploy:deploy" \
    || fail "authorized_keys ownership wrong"
[ -s /home/deploy/.ssh/authorized_keys ] \
    && pass "authorized_keys non-empty ($(grep -c . /home/deploy/.ssh/authorized_keys) key(s))" \
    || fail "authorized_keys is EMPTY — deploy cannot log in"

# --- Worker B: SSH hardening (effective config) --------------------------------
SSHD_T="$(sshd -T 2>/dev/null || true)"
sshd_check() { echo "$SSHD_T" | grep -qi "^$1 $2\$"; }
sshd_check permitrootlogin no \
    && pass "sshd: PermitRootLogin no" \
    || fail "sshd: PermitRootLogin is not 'no' (lockdown pending? re-run with HARDEN_SSH_CONFIRM=yes)"
sshd_check passwordauthentication no \
    && pass "sshd: PasswordAuthentication no" \
    || fail "sshd: PasswordAuthentication is not 'no'"
sshd_check kbdinteractiveauthentication no \
    && pass "sshd: KbdInteractiveAuthentication no" \
    || fail "sshd: KbdInteractiveAuthentication is not 'no'"
sshd_check permitemptypasswords no \
    && pass "sshd: PermitEmptyPasswords no" \
    || fail "sshd: PermitEmptyPasswords is not 'no'"
sshd_check maxauthtries 3 \
    && pass "sshd: MaxAuthTries 3" \
    || fail "sshd: MaxAuthTries is not 3"
sshd_check clientaliveinterval 300 \
    && pass "sshd: ClientAliveInterval 300" \
    || fail "sshd: ClientAliveInterval is not 300"
sshd_check clientalivecountmax 2 \
    && pass "sshd: ClientAliveCountMax 2" \
    || fail "sshd: ClientAliveCountMax is not 2"

# --- Worker C: Firewall --------------------------------------------------------
ufw status 2>/dev/null | grep -q 'Status: active' \
    && pass "UFW is active" \
    || fail "UFW is not active"
for port in 22 80 443; do
    ufw status | grep -qE "^${port}/tcp[[:space:]]+ALLOW" \
        && pass "UFW allows ${port}/tcp" \
        || fail "UFW does not allow ${port}/tcp"
done
ufw status | grep -qE '^22/tcp \(v6\)' \
    && pass "UFW IPv6 rules present" \
    || fail "UFW IPv6 rules missing"
grep -q '^IPV6=yes' /etc/default/ufw \
    && pass "UFW IPv6 support enabled" \
    || fail "UFW IPv6 support disabled in /etc/default/ufw"

# --- Worker D: Fail2Ban --------------------------------------------------------
systemctl is-active --quiet fail2ban \
    && pass "Fail2Ban service running" \
    || fail "Fail2Ban service not running"
systemctl is-enabled --quiet fail2ban \
    && pass "Fail2Ban enabled at boot" \
    || fail "Fail2Ban not enabled at boot"
fail2ban-client status sshd >/dev/null 2>&1 \
    && pass "Fail2Ban sshd jail active" \
    || fail "Fail2Ban sshd jail not active"

# --- Worker E: Automatic updates ------------------------------------------------
apt-config dump 2>/dev/null | grep -q 'APT::Periodic::Unattended-Upgrade "1"' \
    && pass "unattended-upgrades enabled" \
    || fail "unattended-upgrades not enabled"
systemctl is-active --quiet apt-daily.timer \
    && pass "apt-daily.timer active" \
    || fail "apt-daily.timer not active"
systemctl is-active --quiet apt-daily-upgrade.timer \
    && pass "apt-daily-upgrade.timer active" \
    || fail "apt-daily-upgrade.timer not active"
grep -q 'Unattended-Upgrade::Automatic-Reboot "false"' /etc/apt/apt.conf.d/50unattended-upgrades \
    && pass "Automatic reboot disabled (notification only)" \
    || fail "Automatic reboot policy not pinned to false"

# --- Worker F: Optimisation ------------------------------------------------------
[ "$(sysctl -n vm.swappiness)" = "10" ] \
    && pass "vm.swappiness = 10" \
    || fail "vm.swappiness is $(sysctl -n vm.swappiness)"
grep -q 'nofile 65535' /etc/security/limits.d/99-vestra.conf 2>/dev/null \
    && pass "nofile limit 65535 configured (applies to new sessions)" \
    || fail "nofile limit not configured"
systemctl show systemd-journald -p ActiveState --value | grep -q '^active$' \
    && pass "systemd-journald running (capped at 500M/1month)" \
    || fail "systemd-journald not running"

# --- Exposure check ---------------------------------------------------------------
LISTENING="$(ss -tlnH | awk '{print $4}' | grep -oE '[0-9]+$' | sort -un | tr '\n' ' ')"
[ "$LISTENING" = "22 " ] \
    && pass "Only port 22 listening (got: ${LISTENING})" \
    || fail "Unexpected listening ports: ${LISTENING} (expected only 22 at this stage)"

echo "=============================================================================="
echo "RESULT: ${PASS} passed, ${FAIL} failed"
echo "=============================================================================="
[ "$FAIL" -eq 0 ]
