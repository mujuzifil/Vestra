# VESTRA Deployment Guide

## Overview

This guide covers deploying the VESTRA e-commerce platform to production.

## Prerequisites

- Docker Engine 24.0+
- Docker Compose 2.20+
- Git
- A server with at least 2 vCPU, 4GB RAM, 20GB storage
- Domain name with DNS configured
- SSL certificate (Let's Encrypt recommended)

## Environment Setup

### 1. Clone Repository

```bash
git clone https://github.com/your-org/vestra.git
cd vestra
```

### 2. Configure Environment

```bash
# Backend
cp backend/.env.example backend/.env
# Edit backend/.env with production values

# Frontend
cp frontend/.env.example frontend/.env.local
# Edit frontend/.env.local with production values
```

### 3. Build and Start

```bash
# Production deployment
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force

# Optimize Laravel
docker compose -f docker-compose.prod.yml exec backend php artisan optimize
```

### 4. Verify Deployment

```bash
# Health check
curl https://api.vestra.com/api/v1/health

# Frontend
curl https://vestra.com/api/health
```

## SSL/TLS Setup

### Using Let's Encrypt with Nginx

```bash
# Uncomment the nginx service in docker-compose.prod.yml
# Configure certbot volumes
# Run: certbot certonly --standalone -d vestra.com -d www.vestra.com
```

## Updating

```bash
# Pull latest code
git pull origin main

# Rebuild and restart
docker compose -f docker-compose.prod.yml up -d --build

# Run migrations if needed
docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force
```

## Rollback

```bash
# Revert to previous image
docker compose -f docker-compose.prod.yml down
docker pull your-dockerhub/vestra-backend:previous-tag
docker compose -f docker-compose.prod.yml up -d
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 502 Bad Gateway | Check PHP-FPM is running: `docker compose exec backend ps aux \| grep php-fpm` |
| Database connection failed | Verify DB credentials in `.env` and MySQL health: `docker compose logs db` |
| Assets not loading | Check Nginx config and storage permissions |
| Payment callbacks failing | Verify `FRONTEND_URL` and webhook URL in Flutterwave dashboard |
