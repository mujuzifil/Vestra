# VESTRA Security Guide

## Security Checklist

### Authentication
- [x] Laravel Sanctum for API token authentication
- [x] Password hashing with bcrypt (12 rounds)
- [x] Login rate limiting (5 attempts per minute per IP)
- [x] Session encryption enabled in production
- [x] HTTPS-only cookies in production
- [x] SameSite=Strict cookies

### Authorization
- [x] Customer orders protected by ownership policies
- [x] Admin panel protected by Filament auth
- [x] Payment transactions scoped to user
- [x] Address CRUD scoped to authenticated user
- [x] Invoice download restricted to order owner

### API Security
- [x] Rate limiting on all public endpoints
- [x] CORS configured for API routes
- [x] CSRF validation for web routes
- [x] Input validation via Form Requests
- [x] Output encoding via API Resources

### Headers
- [x] Content-Security-Policy (CSP)
- [x] X-Frame-Options: DENY
- [x] X-Content-Type-Options: nosniff
- [x] Referrer-Policy: strict-origin-when-cross-origin
- [x] Permissions-Policy
- [x] HSTS (production)
- [x] Server tokens removed

### Payment Security
- [x] Webhook signature validation (Flutterwave)
- [x] Idempotent callback processing
- [x] Transaction reference uniqueness
- [x] Order ownership verification before payment

### Infrastructure
- [x] TrustProxies for load balancers/CDNs
- [x] Docker non-root user for frontend
- [x] PHP-FPM security limits
- [x] Nginx security headers
- [x] Hidden file access denied

## Incident Response

### Suspected Breach
1. Enable maintenance mode: `php artisan down`
2. Rotate all API keys and secrets
3. Review access logs for anomalies
4. Force password resets for affected users
5. Notify stakeholders per data protection requirements

### DDoS Attack
1. Enable Cloudflare or similar DDoS protection
2. Scale horizontally if needed
3. Block malicious IPs at firewall level
4. Contact hosting provider for upstream filtering

## Security Contacts

- Security issues: security@vestra.com
- Emergency: +256 707 128 442
