# TASK 6.8: Backup & Restore

Phase: 6 | Effort: 4-6h | Next: 6.9

Backup plugin data (consents, DSR, docs, vendors) to JSON bundle; restore with validation.
- Integrity checks: checksums per file/chunk; schema/version metadata; option to encrypt at rest; redact/exclude PII on export.
- Restore supports staging-only safety flag; dry-run report; refusal if schema mismatch unless forced with migration.

Success: backup file created with checksum; dry-run passes; restore reproduces data in staging with PII redaction option verified.
