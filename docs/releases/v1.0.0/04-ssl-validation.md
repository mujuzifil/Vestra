# RG1 — SSL Validation

## Objective

Verify TLS certificates, HTTPS trust, and auto-renewal for all production domains.

## Certificate Issuance / Renewal

```bash
cd /opt/vestra
COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env.production"
$COMPOSE run --rm --entrypoint "" certbot \
  certbot certonly --webroot -w /var/www/certbot -d admin.vestradetergents.com \
  --agree-tos --non-interactive --email vestradetergents@gmail.com
$COMPOSE exec nginx nginx -s reload
```

| Step | Status |
|------|--------|
| Certificate issued/renewed for admin.vestradetergents.com | |
| Nginx reloaded | |

## Certificate Details

```bash
echo | openssl s_client -connect vestradetergents.com:443 -servername vestradetergents.com 2>/dev/null | openssl x509 -noout -subject -issuer -dates
echo | openssl s_client -connect api.vestradetergents.com:443 -servername api.vestradetergents.com 2>/dev/null | openssl x509 -noout -subject -issuer -dates
echo | openssl s_client -connect admin.vestradetergents.com:443 -servername admin.vestradetergents.com 2>/dev/null | openssl x509 -noout -subject -issuer -dates
```

| Domain | Subject CN | Issuer | Not Before | Not After | Status |
|--------|------------|--------|------------|-----------|--------|
| vestradetergents.com | | | | | |
| api.vestradetergents.com | | | | | |
| admin.vestradetergents.com | | | | | |

## Browser Trust

| Domain | Chrome | Firefox | Safari / Edge | Status |
|--------|--------|---------|---------------|--------|
| vestradetergents.com | | | | |
| api.vestradetergents.com | | | | |
| admin.vestradetergents.com | | | | |

## TLS Configuration

| Property | Expected | Actual |
|----------|----------|--------|
| Minimum TLS version | 1.2 | |
| Modern cipher suite | Yes | |
| Complete certificate chain | Yes | |
| HSTS enabled | Yes | |

## Auto-Renewal

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production run --rm --entrypoint "" certbot \
  certbot renew --dry-run
```

| Result | Status |
|--------|--------|
| Renewal dry-run succeeded | |

## Findings

| Finding | Severity | Action |
|---------|----------|--------|
| | | |

## Conclusion

- [ ] Certificates valid for all domains.
- [ ] Browser trust confirmed.
- [ ] TLS configuration modern.
- [ ] Auto-renewal dry-run successful.
- [ ] HSTS enabled.
