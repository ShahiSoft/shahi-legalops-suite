# TASK 7.6: Packaging & Release

Phase: 7 | Effort: 4-6h | Next: 7.7

Prepare release artifacts.
- Bump version, changelog, tag; build zip; verify composer autoload, WP plugin headers, text domain, and POT/PO/MO inclusion.
- Run lint/tests, security scan, and final QA checklist; ensure optional modules flagged as such.
- License/attribution review for assets, fonts, JS/PHP deps; include NOTICE if needed.

Success: Installable zip produced; scans clean; tag pushed; release notes reflect features/known issues.

Execution workflow (copy/paste):
- Bump version/changelog; run tests/lints/security scans; build zip; verify headers/text domain/i18n assets.
- Smoke install the zip on fresh WP; verify optional modules toggles visible.
- Tag and publish release notes with features/known issues.
