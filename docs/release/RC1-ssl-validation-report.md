# RC1 — SSL Validation Report

## Objective

Verify that HTTPS is operational for all production domains and that the Let's Encrypt certificate for `admin.vestradetergents.com` is issued, trusted, and auto-renewable.

## Certificate Issuance

Run on the production VPS:

```bash
cd /opt/vestra
COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env.production"
$COMPOSE run --rm --entrypoint "" certbot \
  certbot certonly --webroot -w /var/www/certbot -d admin.vestradetergents.com \
  --agree-tos --non-interactive --email vestradetergents@gmail.com
$COMPOSE exec nginx nginx -s reload
```

## Certificate Validation

```bash
echo | openssl s_client -connect admin.vestradetergents.com:443 -servername admin.vestradetergents.com 2>/dev/null | openssl x509 -noout -subject -issuer -dates
```

Expected output:

- `subject=CN = admin.vestradetergents.com`
- `issuer=... Let's Encrypt ...`
- `notBefore` and `notAfter` within a 90-day window

## Domain Checks

| Domain | Command | Expected Result | Status |
|--------|---------|-----------------|--------|
| vestradetergents.com | `openssl s_client -connect vestradetergents.com:443 -servername vestradetergents.com` | Valid cert, CN matches | |
| api.vestradetergents.com | `openssl s_client -connect api.vestradetergents.com:443 -servername api.vestradetergents.com` | Valid cert, CN matches | |
| admin.vestradetergents.com | `openssl s_client -connect admin.vestradetergents.com:443 -servername admin.vestradetergents.com` | Valid cert, CN matches | |

## Browser Trust

| Domain | Browser Result | Status |
|--------|----------------|--------|
| https://vestradetergents.com | No warnings | |
| https://api.vestradetergents.com | No warnings | |
| https://admin.vestradetergents.com | No warnings | |

## Auto-Renewal

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production run --rm --entrypoint "" certbot \
  certbot renew --dry-run
```

Expected: certbot reports that renewal is possible.

## TLS Details

| Property | Expected | Actual |
|----------|----------|--------|
| Protocol | TLS 1.2 or 1.3 | |
| Cipher | Modern forward-secret cipher | |
| Certificate chain | Complete, no missing intermediates | |
| HSTS | `max-age=31536000; includeSubDomains; preload` | |

## Conclusion

- [ ] Certificate issued for `admin.vestradetergents.com`.
- [ ] All three domains load over HTTPS without warnings.
- [ ] Certificate chain is complete.
- [ ] Auto-renewal dry-run succeeds.
- [ ] HSTS enabled.
