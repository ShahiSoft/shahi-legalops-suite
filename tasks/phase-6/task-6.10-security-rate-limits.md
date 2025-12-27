# TASK 6.10: Security & Rate Limiting

Phase: 6 | Effort: 4-6h | Next: 6.11

Harden APIs.
- Rate limits per IP/tenant for public endpoints (banner, DSR submit/verify, webhook callbacks) with burst + sustained buckets; log and surface 429s.
- Nonce/captcha options; IP and ASN blocking for abuse; optional allowlists; audit log of security events.
- Security headers for REST responses; validate payload schemas; protect against replay and request smuggling.

Success: abusive requests throttled; captcha/nonce blocks scripted abuse; security events logged; tests cover limit and block paths.
