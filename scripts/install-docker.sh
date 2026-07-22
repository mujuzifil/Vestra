#!/bin/bash
# ==============================================================================
# VESTRA — Stage 17.3: Docker Engine & Compose installation
# ==============================================================================
# Usage (on the VPS):
#   sudo ./scripts/install-docker.sh
#
# Installs Docker Engine from the official Docker apt repository:
#   docker-ce, docker-ce-cli, containerd.io,
#   docker-buildx-plugin, docker-compose-plugin
#
# Idempotent: already-installed components are skipped; safe to re-run.
# ==============================================================================
set -euo pipefail

log()  { echo -e "\033[0;34m[docker-install]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[docker-install]\033[0m $*"; }
fail() { echo -e "\033[0;31m[docker-install]\033[0m $*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || fail "Must run as root (use sudo)."

export DEBIAN_FRONTEND=noninteractive

# ------------------------------------------------------------------------------
# Official Docker apt repository
# ------------------------------------------------------------------------------
if [ ! -f /etc/apt/sources.list.d/docker.list ]; then
    log "Adding Docker apt repository..."
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
        | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
        > /etc/apt/sources.list.d/docker.list
    apt-get update -qq
    ok "Repository added."
else
    ok "Docker apt repository already configured."
fi

# ------------------------------------------------------------------------------
# Packages
# ------------------------------------------------------------------------------
log "Installing Docker Engine, Buildx and Compose plugin..."
apt-get install -y -qq \
    docker-ce docker-ce-cli containerd.io \
    docker-buildx-plugin docker-compose-plugin
ok "Packages installed."

# ------------------------------------------------------------------------------
# Service + access
# ------------------------------------------------------------------------------
systemctl enable --now docker
id deploy &>/dev/null && usermod -aG docker deploy && ok "deploy added to docker group (takes effect on next login)."

echo
ok "Docker installed: $(docker --version)"
ok "Compose plugin: $(docker compose version)"
