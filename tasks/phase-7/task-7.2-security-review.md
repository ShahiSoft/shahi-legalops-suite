# TASK 7.2: Security Review & Pen Test

Phase: 7 | Effort: 6-8h | Next: 7.3

Security hardening.
- Review REST permissions/nonce/cap checks; authz on consent/DSR/doc/vendor/admin endpoints; confirm rate limits and captcha paths from [task-6.10-security-rate-limits.md](../phase-6/task-6.10-security-rate-limits.md).
- Static/dynamic checks: WPScan, npm/yarn audit, composer audit, phpstan/phpcs, eslint; manual XSS/CSRF/SQLi; file upload paths; JSON schema validation on inputs.
- Headers: CSP guidance for custom assets, HSTS, X-Frame-Options/SameSite on cookies, CORS rules; verify webhook signature and replay protection.
- Secrets handling: API keys stored hashed/encrypted where applicable; ensure logs redact tokens; backup archives respect encryption/redaction.

Success: No critical/high open; medium with mitigation accepted; report with findings, fixes, CVSS-ish rating, retest evidence.

Execution workflow (copy/paste):
- Run WPScan + dependency audits; manual XSS/CSRF/SQLi passes; verify rate limits and webhook signatures.
- Document findings with severity and fixes; rerun scans after patches.
- Produce short security report and signoff.
