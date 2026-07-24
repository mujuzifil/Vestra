# RC1 — Go-Live Certification

## Release Candidate

| Item | Value |
|------|-------|
| Release candidate | RC1 |
| Certification date | |
| Platform | VESTRA Detergents |
| Production domains | `vestradetergents.com`, `api.vestradetergents.com`, `admin.vestradetergents.com` |

## Certification Criteria

| Criterion | Required | Evidence | Status |
|-----------|----------|----------|--------|
| Production deployment successful | `deploy.sh` completed, containers healthy | `RC1-production-deployment-report.md` | |
| Environment validated | All required variables present, compose config valid | `RC1-environment-validation-report.md` | |
| SSL operational | HTTPS on all domains, valid certificates | `RC1-ssl-validation-report.md` | |
| Domains validated | DNS, redirects, headers, response codes | `RC1-domain-validation-report.md` | |
| Regression tests passed | Website, API, admin, redirects | `RC1-regression-test-report.md` | |
| Security validated | HTTPS, HSTS, CSRF, cookies, sessions, rate limits | `RC1-security-validation-report.md` | |
| Performance validated | Key pages measured, resources within limits | `RC1-performance-report.md` | |
| Monitoring validated | Containers, DB, Redis, queue, scheduler, logs, backups | `RC1-production-monitoring-report.md` | |
| Smoke test passed | End-to-end customer journey | `RC1-smoke-test-report.md` | |

## Go-Live Decision

| Decision | Status |
|----------|--------|
| Release candidate approved | |
| Release candidate rejected | |

## Conditions for Approval

All of the following must be true:

- [ ] No critical defects.
- [ ] No deployment blockers.
- [ ] No security blockers.
- [ ] SSL operational on all domains.
- [ ] Admin portal accessible on dedicated subdomain.
- [ ] Legacy admin redirects correct.
- [ ] Customer website operational.
- [ ] REST API operational.
- [ ] Payments operational.
- [ ] Order tracking operational.
- [ ] Admin fulfilment operational.

## Sign-Off

| Role | Name | Date | Signature/Approval |
|------|------|------|--------------------|
| Release Manager | | | |
| Technical Lead | | | |
| Security Lead | | | |
| Product Owner | | | |

## Next Steps

Upon approval, development may proceed to **Stage 18.6 — Customer Account, Profile & Self-Service Portal**.

If rejected, attach the rejection reason and required remedial actions below.

## Rejection Reason / Remedial Actions

| Issue | Action Owner | Target Date |
|-------|--------------|-------------|
| | | |

---

**Certification Result:**

☐ **APPROVED** — RC1 is certified for production.

☐ **REJECTED** — RC1 requires remedial action before certification.
